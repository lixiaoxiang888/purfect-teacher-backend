<?php


namespace App\Http\Controllers\Teacher\Community;


use App\Dao\Forum\ForumDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\ForumRequest;

class DynamicController extends Controller
{
    public function index(ForumRequest $request) {
        $schoolId = $request->getSchoolId();
        $dao = new ForumDao();
        $list = $dao->getForumBySchoolId($schoolId);

        $this->dataForView['pageTitle'] = '动态列表';
        $this->dataForView['list'] = $list;
        return view('teacher.community.dynamic.index', $this->dataForView);
    }


    public function edit(ForumRequest $request) {
        $forumId = $request->get('id');
        $dao = new ForumDao();
        $info = $dao->find($forumId);
        $this->dataForView['forum'] = $info;
        return view('teacher.community.dynamic.edit', $this->dataForView);
    }


    public function delete() {

    }

}
