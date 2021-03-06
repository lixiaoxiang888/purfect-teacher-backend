<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 3/12/19
 * Time: 11:08 AM
 */

namespace App\Dao\Pipeline;
use App\Dao\NetworkDisk\MediaDao;
use App\Models\Pipeline\Flow\Action;
use App\Models\Pipeline\Flow\ActionAttachment;
use App\Models\Pipeline\Flow\ActionOption;
use App\Models\Pipeline\Flow\Copys;
use App\Models\Pipeline\Flow\Flow;
use App\Models\Pipeline\Flow\UserFlow;
use App\User;
use App\Utils\JsonBuilder;
use App\Utils\Pipeline\IAction;
use App\Utils\Pipeline\IFlow;
use App\Utils\Pipeline\IUserFlow;
use App\Utils\ReturnData\IMessageBag;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActionDao
{
    /**
     * @param User $user
     * @param $userFlowId
     * @return IMessageBag
     */
    public function cancelUserFlow($user, $userFlowId){
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        // 定位指定的 action
        /**
         * @var UserFlow $userFlow
         */
        if($user->isSchoolAdminOrAbove()){
            $userFlow = UserFlow::find($userFlowId);
        }
        else{
            $userFlow = UserFlow::where('id',$userFlowId)->where('user_id',$user->id)->first();
        }

        if($userFlow){
            // 找到了 action, 那么就删除此 action 已经相关的操作
            DB::beginTransaction();
            try{
                $actions = $userFlow->actions;

                foreach ($actions as $action) {
                    $this->delete($action->id);
                }
                $userFlow->delete();

                DB::commit();
                $bag->setCode(JsonBuilder::CODE_SUCCESS);
            }
            catch (\Exception $exception){
                $bag->setMessage($exception->getMessage());
                DB::rollBack();
            }
        }
        else{
            $bag->setMessage('您无权进行此操作');
        }
        return $bag;
    }

    /**
     * @param $result
     * @param $flow
     * @param null $user
     * @return Action
     */
    public function getByFlowAndResult($result, $flow, $user){
        $where = [
            ['result','=',$result]
        ];
        if(is_object($flow)){
            $flowId = $flow->id;
        }
        else{
            $flowId = $flow;
        }
        $where[] = ['flow_id','=',$flowId];

        if(is_object($user)){
            $userId = $user->id;
        }
        else{
            $userId = $user;
        }
        $where[] = ['user_id','=',$userId];
        return Action::where($where)
            ->with('options')
            ->with('attachments')
            ->first();
    }

    /**
     * @param $data
     * @param IUserFlow|int $userFlow: 用户流程接口的对象或者用户流程接口 ID
     * @return Action
     */
    public function create($data, $userFlow){
        DB::beginTransaction();
        try{
            // 为了可以让流程的动作形成关联关系, 必须生成一个标识当前流程的 transaction id.
            $data['transaction_id'] = $userFlow->id ?? $userFlow;

            $action = Action::create($data);
            if(isset($data['attachments'])){
                foreach ($data['attachments'] as $attachment) {
                    if(is_array($attachment)){
                        $attachment['action_id'] = $action->id;
                        ActionAttachment::create($attachment);
                    }
                    elseif (is_string($attachment) || is_int($attachment)){
                        // 可能是手机 APP 调用传来的
                        $media = (new MediaDao())->getMediaById($attachment);
                        if($media){
                            ActionAttachment::create(
                                [
                                    'action_id'=>$action->id,
                                    'media_id'=>$attachment,
                                    'url'=>$media->url,
                                    'file_name'=>$media->file_name,
                                ]
                            );
                        }
                    }
                }
            }
            // 保存申请人提交的必选项
            if(isset($data['options'])){
                foreach ($data['options'] as $option) {
                    $actionOptionData = [
                        'action_id' => $action->id,
                        'option_id' => $option['id'],
                        'value' => is_string($option['value']) ? $option['value'] : json_encode($option['value']),
                    ];
                    ActionOption::create($actionOptionData);
                }
            }

            DB::commit();
            return $action;
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::alert('创建工作流步骤的 action 失败',['msg'=>$exception->getMessage(),'data'=>$data]);
            return null;
        }
    }

    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function delete($id){
        DB::beginTransaction();
        try{
            Action::where('id',$id)->delete();
            ActionAttachment::where('action_id',$id)->delete();
            ActionOption::where('action_id',$id)->delete();
            DB::commit();
            return true;
        }catch (\Exception $exception){
            DB::rollBack();
            return false;
        }
    }

    /**
     * 获取给定用户的所有发起的流程
     * @param $user
     * @return Collection
     */
    public function getFlowsWhichStartBy($user, $position = 0, $keyword =''){
        if (!$position) {
            return UserFlow::where('user_id',$user->id??$user)
                ->with('flow')
                ->orderBy('id','desc')
                ->get();
        }else {
            $flowIdArr = Flow::whereIn('type', array_keys(Flow::getTypesByPosition($position)))->pluck('id')->toArray();
            return UserFlow::where('user_id',$user->id??$user)
                ->whereIn('flow_id', $flowIdArr)
                ->with('flow')
                ->orderBy('id','desc')
                ->get();
        }
    }

    /**
     * 获取抄送我的流程
     * @param $user
     * @return mixed
     */
    public function getFlowsWhichCopyTo($user, $position = 0){
        if (!$position) {
            return UserFlow::whereHas('copys', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('flow')
                ->orderBy('id','desc')
                ->get();
        }else {
            $flowIdArr = Flow::whereIn('type', array_keys(Flow::getTypesByPosition($position)))->pluck('id')->toArray();
            return UserFlow::whereHas('copys', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('flow')
                ->whereIn('flow_id', $flowIdArr)
                ->orderBy('id','desc')
                ->get();
        }

    }

    /**
     * 我审批的
     * @param $user
     * @return mixed
     */
    public function getFlowsWhichMyProcessed($user, $position = 0){
        if (!$position) {
            return UserFlow::whereHas('actions', function ($query) use ($user) {
              $query->where('user_id', $user->id)->where('result', '>', IAction::RESULT_PENDING);
            })->with('flow')
              ->orderBy('id','desc')
              ->get();
        }else {
            $flowIdArr = Flow::whereIn('type', array_keys(Flow::getTypesByPosition($position)))->pluck('id')->toArray();
            return UserFlow::whereHas('actions', function ($query) use ($user) {
              $query->where('user_id', $user->id)->where('result', '>', IAction::RESULT_PENDING);
            })->with('flow')
                ->whereIn('flow_id', $flowIdArr)
                ->orderBy('id','desc')
                ->get();
        }
    }

    /**
     * 获取给定用户的所有等待审核的流程
     * @param $user
     * @return Collection
     */
    public function getFlowsWaitingFor($user, $position = 0, $keyword= ''){
        if (!$position) {
            return Action::where('user_id',$user->id??$user)
                ->where('result','=',IAction::RESULT_PENDING)
                ->with('flow')
                ->with('userFlow')
                ->orderBy('id','desc')
                ->get();
        }else {
            $flowIdArr = Flow::whereIn('type', array_keys(Flow::getTypesByPosition($position)))->pluck('id')->toArray();
            return Action::where('user_id',$user->id??$user)
                ->where('result','=',IAction::RESULT_PENDING)
                ->whereIn('flow_id', $flowIdArr)
                ->with('flow')
                ->with('userFlow')
                ->orderBy('id','desc')
                ->get();
        }
    }

    /**
     * 根据 user flow 的 id 获取历史记录
     * @param $userFlowId
     * @param $actionsOnly
     * @return Collection
     */
    public function getHistoryByUserFlow($userFlowId, $actionsOnly = false){
        if($actionsOnly){
            return Action::where('transaction_id',$userFlowId)
                ->with('options')
                ->orderBy('id','desc')
                ->get();
        }
        return Action::where('transaction_id',$userFlowId)
            ->with('node')
            ->with('options')
            ->orderBy('id','desc')
            ->get();
    }

    /**
     * 根据 user flow 的 id 获取最后一个动作
     * @param $userFlowId
     * @return Action
     */
    public function getLastActionByUserFlow($userFlowId){
        return Action::where('transaction_id',$userFlowId)
            ->with('node')
            ->with('options')
            ->with('attachments')
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 根据 user flow 的 id 获取第一个动作, 就是整个流程发起的动作
     * @param $userFlowId
     * @return Action
     */
    public function getFirstActionByUserFlow($userFlowId){
        return Action::where('transaction_id',$userFlowId)
            ->with('node')
            ->with('options')
            ->with('attachments')
            ->orderBy('id','asc')
            ->first();
    }

    public function getActionByUserFlowAndUserId($userFlowId, $userId){
        return Action::where('transaction_id',$userFlowId)
            ->where('user_id', $userId)
            ->with('node')
            ->with('options')
            ->with('attachments')
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * @param $actionId
     * @param $userId
     * @return Action
     */
    public function getByActionIdAndUserId($actionId, $userId){
        return Action::where('id',$actionId)
            ->where('user_id',$userId)
            ->with('options')
            ->with('attachments')
            ->first();
    }

    /**
     * @param $actionId
     * @return Action
     */
    public function getByActionId($actionId){
        return Action::where('id',$actionId)
            ->with('options')
            ->with('attachments')
            ->first();
    }

    public function getCountWaitProcessUsers($nodeId, $userFlowId){
        return Action::where([
            'node_id' => $nodeId,
            'result' => IAction::RESULT_PENDING,
            'transaction_id' => $userFlowId
        ])->count();
    }
}
