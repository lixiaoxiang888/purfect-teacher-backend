<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 8/12/19
 * Time: 4:05 PM
 */

namespace App\BusinessLogic\Pipeline\Flow\Impl;

use App\BusinessLogic\Pipeline\Flow\Contracts\IFlowLogic;
use App\Models\Pipeline\Flow\UserFlow;
use App\Utils\Pipeline\IAction;
use App\Utils\Pipeline\IFlow;
use App\Utils\Pipeline\IUserFlow;
use App\User;
use App\Dao\Pipeline\ActionDao;
use App\Utils\ReturnData\MessageBag;
use App\Utils\JsonBuilder;
use Illuminate\Support\Facades\DB;
use App\Utils\Pipeline\INode;
use App\Utils\ReturnData\IMessageBag;

abstract class GeneralFlowsLogic implements IFlowLogic
{
    /**
     * @var User $user
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public abstract function getMyFlows();

    /**
     * 获取所有等待用户处理的操作
     * @return IAction[]|\Illuminate\Database\Eloquent\Collection
     */
    public function waitingForMe()
    {
        $actionDao = new ActionDao();
        return $actionDao->getFlowsWaitingFor($this->user);
    }

    /**
     * 获取我发起的流程
     * @return IAction[]|\Illuminate\Database\Eloquent\Collection
     */
    public function startedByMe()
    {
        $actionDao = new ActionDao();
        return $actionDao->getFlowsWhichStartBy($this->user);
    }

    /**
     * @param IFlow $flow
     * @return IMessageBag
     */
    public function open(IFlow $flow)
    {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        if($headNode = $flow->canBeStartBy($this->user)){
            $bag->setData([
                'node'=>$headNode
            ]);
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
        }else{
            $bag->setMessage('用户没有权限打开本流程');
        }
        return $bag;
    }

    /**
     * 启动流程的逻辑
     * @param IFlow $flow
     * @param array $actionData
     * @return IMessageBag
     */
    public function start(IFlow $flow, $actionData)
    {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);

        /**
         * @var INode $startNode
         */
        $startNode = $flow->canBeStartBy($this->user);

        if($startNode){
            // 用户可以启动流程, 那么就获得了启动流程的第一个步骤节点
            $actionData['node_id'] = $startNode->id;
            $actionData['user_id'] = $this->user->id;
            $actionData['result'] = IAction::RESULT_PASS; // 启动工作没有审核, 直接就是 pass 的状态

            $dao = new ActionDao();
            DB::beginTransaction();
            try{
                // 先为当前的用户创建一个用户流程

                /**
                 * @var IUserFlow $userFlow
                 */
                $userFlow = UserFlow::create([
                    'flow_id'=>$flow->id,
                    'user_id'=>$this->user->id,
                    'user_name'=>$this->user->getName(),
                ]);

                // 用户流程创建之后, 创建该流程的第一个 action
                $action = $dao->create($actionData, $userFlow);

                // 第一个 action 创建成功后, 找到流程的第二步, 然后针对第二步所依赖的审批人(handlers), 创建需要执行的 actions
                // 生成下一步需要的操作
                $handler = $startNode->getHandler();
                if($handler){
                    // 根据当前提交 action 的用户和流程, 创建所有需要的下一步流程
                    $handler->handle($this->user, $action);
                }

                $bag->setCode(JsonBuilder::CODE_SUCCESS);
                $bag->setData([
                    'action'=>$action,
                    'node'=>$startNode,
                ]);
                DB::commit();
            }
            catch (\Exception $exception){
                $bag->setMessage($exception->getMessage().', line='.$exception->getLine());
                DB::rollBack();
            }
        }
        else{
            $bag->setMessage('用户没有权限启动本流程');
        }
        return $bag;
    }
}