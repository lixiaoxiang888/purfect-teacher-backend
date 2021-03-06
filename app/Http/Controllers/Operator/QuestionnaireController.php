<?php


namespace App\Http\Controllers\Operator;


use App\Dao\Questionnaire\QuestionnaireDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Http\Requests\School\QuestionnaireRequest;
use App\Models\Questionnaire\Questionnaire;
use App\Utils\FlashMessageBuilder;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 管理问卷调查表的 action
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function management(MyStandardRequest $request){

        $dao = new QuestionnaireDao();
        $questionnaires = $dao->getQuestionnaireBySchoolId($request->session()->get('school.id'));
        $data = [];
        foreach ($questionnaires as $questionnaire)
        {
            $data[$questionnaire->id]['total'] = count($questionnaire->result()->get());
            $data[$questionnaire->id]['first']  = count($questionnaire->result()->where('result',1)->get());
            $data[$questionnaire->id]['second'] = count($questionnaire->result()->where('result',2)->get());
            $data[$questionnaire->id]['third']  = count($questionnaire->result()->where('result',3)->get());
        }


        $this->dataForView['questionnaires'] = $questionnaires;
        $this->dataForView['results'] = $data;
        return view('school_manager.questionnaire.questionnaires', $this->dataForView);

    }

    /**
     * 加载添加问卷的表单
     */
    public function add(){
        $this->dataForView['questionnaire'] = [];
        return view('school_manager.questionnaire.add', $this->dataForView);
    }
    /**
     * 加载添加问卷的表单
     */
    public function edit(Request $request){
        $dao = new QuestionnaireDao();
        $questionnaire = $dao->getQuestionnaireById($request->id);
        $this->dataForView['questionnaire'] = $questionnaire;
        return view('school_manager.questionnaire.edit', $this->dataForView);
    }


    /**
     * 保存问卷的方法
     * @param QuestionnaireRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(QuestionnaireRequest $request){
        $requestArr = $request->validated();
        $questionnaireData = $requestArr['questionnaire'];
        $questionnaireData['school_id'] = $request->session()->get('school.id');
        $dao = new QuestionnaireDao();
        if(isset($questionnaireData['id'])){
            $result = $dao->update($questionnaireData);
        }
        else{
            $result = $dao->create($questionnaireData);
        }
        if($result->getCode() == 1000){
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,$result->getData()->title.'问卷保存成功');
            return redirect()->route('school_manager.contents.questionnaire');
        }else{
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'无法保存问卷');
            return redirect()->back();
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $dao = new QuestionnaireDao();
        $result = $dao->delete($id);
        return redirect()->back();
    }
}
