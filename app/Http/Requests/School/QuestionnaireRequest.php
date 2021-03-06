<?php


namespace App\Http\Requests\School;


use App\Http\Requests\MyStandardRequest;

class QuestionnaireRequest extends MyStandardRequest
{
    public function rules()
    {
        return [
            'questionnaire.id'                    => 'nullable|numeric',
            'questionnaire.title'                 => ['required',  'max:255'],
            'questionnaire.detail'                => ['required'],
            'questionnaire.first_question_info'   => ['required',  'max:255'],
            'questionnaire.second_question_info'  => ['required',  'max:255'],
            'questionnaire.third_question_info'   => ['required',  'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'questionnaire.title.max'  => '最多255个汉字',
            'questionnaire.first_question_info.max'  => '最多255个汉字',
            'questionnaire.second_question_info.max'  => '最多255个汉字',
            'questionnaire.third_question_info.max'  => '最多255个汉字',
        ];
    }

}
