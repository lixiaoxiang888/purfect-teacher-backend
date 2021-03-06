<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 3/12/19
 * Time: 3:50 PM
 */

namespace App\Http\Controllers\Operator\Pipeline;
use App\Dao\Pipeline\FlowDao;
use App\Dao\Pipeline\HandlerDao;
use App\Dao\Pipeline\NodeDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pipeline\FlowRequest;
use App\Models\Pipeline\Flow\Flow;
use App\Models\Pipeline\Flow\NodeAttachment;
use App\Models\Users\GradeUser;
use App\Utils\JsonBuilder;
use App\Utils\Pipeline\IFlow;

class FlowsController extends Controller
{
    /**
     * @param FlowRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function manager(FlowRequest $request){
        $this->dataForView['pageTitle'] = '工作流程管理';
        $dao = new FlowDao();
        $this->dataForView['groupedFlows'] = $dao->getGroupedFlows($request->session()->get('school.id'));
        $this->dataForView['lastNewFlow'] = $request->getLastNewFlow(); // 上次可能刚创建了新流程
        return view('school_manager.pipeline.flow.manager', $this->dataForView);
    }

    public function handler(FlowRequest $request){
        $this->dataForView['pageTitle'] = '工作流程管理';
        $dao = new FlowDao();
        $flow = $dao->getById($request->get('flow_id'));
        $this->dataForView['flow'] = $flow;
        return view('school_manager.pipeline.flow.handler', $this->dataForView);
    }
    public function option(FlowRequest $request){
        $this->dataForView['pageTitle'] = '工作流程管理';
        $dao = new FlowDao();
        $flow = $dao->getById($request->get('flow_id'));
        $this->dataForView['flow'] = $flow;
        return view('school_manager.pipeline.flow.option', $this->dataForView);
    }

    /**
     * 加载某个位置的全部流程
     * @param FlowRequest $request
     * @return string
     */
    public function load_flows(FlowRequest $request){
        $schoolId = $request->session()->get('school.id');
        $position = $request->getPosition();
        $dao = new FlowDao();
        $list = $dao->getGroupedFlows($schoolId,array_keys(Flow::getTypesByPosition($position)));
        return JsonBuilder::Success($list);
    }

    /**
     * 加载某个流程的所有步骤
     * @param FlowRequest $request
     * @return string
     */
    public function load_nodes(FlowRequest $request){
        $dao = new FlowDao();
        $flow = $dao->getById($request->get('flow_id'));
        if($flow){
            $flow->position = Flow::getPositionByType($flow->type);
            $nodes = $flow->getSimpleLinkedNodes();
            return JsonBuilder::Success(['flow'=>$flow,'nodes'=>$nodes]);
        }
        return JsonBuilder::Error();
    }

    /**
     * 保存流程
     * @param FlowRequest $request
     * @return string
     */
    public function save_flow(FlowRequest $request){
        $flow = $request->getFlowFormData();
        if(empty($flow['id'])){
            // 创建新流程
            $node = $request->getNewFlowFirstNode();
            $dao = new FlowDao();
            $result = $dao->create($flow, '', $node);
            return $result->isSuccess() ?
                JsonBuilder::Success(['id'=>$result->getData()->id]) :
                JsonBuilder::Error($result->getMessage());
        }else {
            //更新流程
            $node = $request->getNewFlowFirstNode();
            $dao = new FlowDao();
            $result = $dao->update($flow, '', $node, $flow['id']);
            return $result->isSuccess() ?
                JsonBuilder::Success(['id'=>$flow['id']]) :
                JsonBuilder::Error($result->getMessage());
        }
    }

    /**
     * 删除流程
     * @param FlowRequest $request
     * @return string
     */
    public function delete_flow(FlowRequest $request)
    {
        $id = $request->get('flow_id');
        $dao = new FlowDao();
        if($dao->delete($id)){
            return JsonBuilder::Success();
        }
        else{
            return JsonBuilder::Error();
        }
    }

    /**
     * 保存流程中的步骤节点
     * @param FlowRequest $request
     * @return string
     */
    public function save_node(FlowRequest $request){
        $nodeData = $request->getNewFlowFirstNode();
        $flowId = $request->get('flow_id');
        $prevNodeId = $request->get('prev_node');

        $flowDao = new FlowDao();
        $flow = $flowDao->getById($flowId);
        $nodeDao = new NodeDao();
        if (!$prevNodeId) {
            //获取第一个节点
            $firstNode = $nodeDao->getHeadNodeByFlow($flow->id);
            $prevNodeId = $firstNode->id;
        }
        $nodeData['name'] = '审批流程';
        //非第一个节点已经不需要区分使用者了所以赋予全部人
        $nodeData['handlers'] = ['教师', '职工', '学生'];
        $prevNode = $nodeDao->getById($prevNodeId);
        if ($prevNode->next_node > 0) {
            //如果前节点定义过审批人 移动到当前handler的审批人里
            $nodeData['notice_to'] = $prevNode->handler->notice_to;
            $nodeData['notice_organizations'] = $prevNode->handler->notice_organizations;
        }else {
            $nodeData['notice_to'] = [];
            $nodeData['notice_organizations'] = [];
        }
        $result = $nodeDao->insert($nodeData, $flow, $prevNode);
        if(is_object($result)){
            $handlerDao = new HandlerDao();
            //创建新的handler使用者
            $handlerDao->create($result, $nodeData);
            //更新前一个handler的审批人为当前使用者
            $upData = [
                'notice_to' => $nodeData['titles'],
                'notice_organizations' => $nodeData['organizations']
            ];
            $handlerDao->update($prevNode, $upData);

            return JsonBuilder::Success(['flow' => $flow,'nodes'=>$flow->getSimpleLinkedNodes()]);
        }
        else{
            return JsonBuilder::Error($result);
        }
    }

    /**
     * 删除流程中的节点
     * @param FlowRequest $request
     * @return string
     */
    public function delete_node(FlowRequest $request){
        $schoolId = $request->get('school_id');
        if(intval($schoolId) !== $request->session()->get('school.id')){
            return JsonBuilder::Error('您无权进行此操作');
        }

        $nodeId = $request->get('node_id');
        $dao = new NodeDao();
        $result = $dao->delete($nodeId);
        if($result === true){
            return JsonBuilder::Success();
        }
        else{
            return JsonBuilder::Error('系统繁忙, 请稍候再试');
        }
    }

    public function save_copy(FlowRequest $request){
        $flowId = $request->get('flow_id');
        $userIdArr = $request->get('users');
        if (Flow::where('id', $flowId)->update(['copy_uids' => implode(';', $userIdArr)])) {
            return JsonBuilder::Success(['copy' =>GradeUser::whereIn('user_id', $userIdArr)->select(['user_id', 'name'])->get()]);
        }else {
            return JsonBuilder::Error('系统繁忙, 请稍候再试');
        }
    }
    public function delete_copy(FlowRequest $request){
        $schoolId = $request->get('school_id');
        if(intval($schoolId) !== $request->session()->get('school.id')){
            return JsonBuilder::Error('您无权进行此操作');
        }
        $flowId = $request->get('flow_id');
        if (Flow::where('id', $flowId)->update(['copy_uids' => null])) {
            return JsonBuilder::Success();
        }else {
            return JsonBuilder::Error('系统繁忙, 请稍候再试');
        }
    }


    /**
     * 更新节点的操作
     * @param FlowRequest $request
     * @return string
     */
    public function update_node(FlowRequest $request){
        $nodeData = $request->getNewFlowFirstNode();
        $flowId = $request->get('flow_id');
        $prevNodeId = $request->get('prev_node');
        $organizationsLeftOver = $request->get('prev_orgs'); // 遗留的组织

        $flowDao = new FlowDao();
        $flow = $flowDao->getById($flowId);

        $nodeDao = new NodeDao();
        $prevNode = $nodeDao->getById($prevNodeId);

        $result = $nodeDao->update($nodeData, $prevNode, $flow, $organizationsLeftOver);

        if($result === true){
            return JsonBuilder::Success(['nodes'=>$flow->getSimpleLinkedNodes()]);
        }
        else{
            return JsonBuilder::Error($result);
        }
    }





    /**
     * 删除步骤所关联的附件
     * @param FlowRequest $request
     * @return string
     */
    public function delete_node_attachment(FlowRequest $request){
        if(NodeAttachment::where('id',$request->get('attachment_id'))->delete()){
            return JsonBuilder::Success();
        }
        else{
            return JsonBuilder::Error();
        }
    }

    /**
     * @param FlowRequest $request
     * @return string
     */
    public function save_option(FlowRequest $request){
        $nodeOptionFormData = $request->getNodeOptionFormData();
        $flowId = $request->get('flow_id');

        if($nodeOptionFormData){
            $dao = new NodeDao();
            $flowDao = new FlowDao();
            $firstNode = $dao->getHeadNodeByFlow($flowId);
            $flow = $flowDao->getById($flowId);
            try{
                $dao->deleteOptionByNode($firstNode->id);
                if ($flow->business) {
                    $businessOptions = Flow::getBusiness($flow->business);
                    $businessIgnore = [];
                    foreach ($businessOptions['options'] as $businessOption) {
                        $businessOption['node_id'] = $firstNode->id;
                        $dao->saveNodeOption($businessOption);
                        $businessIgnore[] = $businessOption['title'];
                    }
                    foreach ($nodeOptionFormData as $nodeData) {
                        if (in_array($nodeData['title'], $businessIgnore)) {
                            continue;
                        }
                        $nodeData['node_id'] = $firstNode->id;
                        $dao->saveNodeOption($nodeData);
                    }
                }else {
                    foreach ($nodeOptionFormData as $nodeData) {
                        $nodeData['node_id'] = $firstNode->id;
                        $dao->saveNodeOption($nodeData);
                    }
                }

                return JsonBuilder::Success();
            }
            catch (\Exception $exception){
                return JsonBuilder::Error($exception->getMessage());
            }
        }
        return JsonBuilder::Error('找不到提交的表单信息');
    }

    public function save_auto_processed(FlowRequest $request){
        $flowId = $request->get('flow_id');
        $autoProcessed = $request->get('auto_processed', 0);
        $dao = new FlowDao();
        $flow = $dao->getById($flowId);
        $flow->auto_processed = $autoProcessed ? 1 : 0;
        $flow->save();
        return JsonBuilder::Success();
    }

    /**
     * @param FlowRequest $request
     * @return string
     */
    public function delete_option(FlowRequest $request){
        $nodeOptionId = $request->get('node_option_id');
        $dao = new NodeDao();
        $result = $dao->deleteOption($nodeOptionId);
        return $result ? JsonBuilder::Success() : JsonBuilder::Error('找不到指定的选项数据');
    }

    public function load_business(FlowRequest $request){
        return JsonBuilder::Success(Flow::getBusiness());
    }

    public function load_titles(FlowRequest $request) {
        $position = $request->getPosition();
        $titleType = $request->get('type');
        $roleType = $request->get('role');
        return JsonBuilder::Success(Flow::getTitlesByType($position, $titleType, $roleType));
    }


}
