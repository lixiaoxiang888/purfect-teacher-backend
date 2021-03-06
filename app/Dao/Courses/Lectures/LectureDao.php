<?php
/**
 * 课件的DAO
 * Author: Justin Wang
 * Email: hi@yue.dev
 */
namespace App\Dao\Courses\Lectures;


use App\User;
use Carbon\Carbon;
use App\Utils\JsonBuilder;
use App\Dao\Users\GradeUserDao;
use App\Models\Courses\Lecture;
use App\Models\Users\GradeUser;
use App\Models\Courses\Homework;
use Illuminate\Support\Facades\DB;
use App\Utils\ReturnData\MessageBag;
use App\Utils\Time\GradeAndYearUtil;
use App\Utils\Misc\ConfigurationTool;
use App\Models\Courses\LectureMaterial;
use App\Models\Courses\LectureMaterialType;
use Illuminate\Database\Eloquent\Collection;

class LectureDao
{
    /**
     * @param $lectureId
     * @return Lecture
     */
    public function getLectureById($lectureId){
        return Lecture::find($lectureId);
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @param $index
     * @return Lecture
     */
    public function getLecturesByCourseAndTeacherAndIndex($courseId, $teacherId,$index){
        $lecture = Lecture::where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->where('idx',$index)
            ->first();
        if(!$lecture){
            $lecture = Lecture::create([
                'course_id'=>$courseId,
                'teacher_id'=>$teacherId,
                'idx'=>$index,
                'title'=>'',
                'summary'=>'',
                'tags'=>'',
            ]);
        }
        return $lecture;
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @return Collection
     */
    public function getLecturesByCourseAndTeacher($courseId, $teacherId){
        return Lecture::where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->orderBy('idx','asc')
            ->get();
    }

    /**
     * 根据课节的id获取其所有课件附件的记录
     * @param $lectureId
     * @return Collection
     */
    public function getLectureMaterials($lectureId){
        return LectureMaterial::where('lecture_id',$lectureId)
            ->orderBy('type','asc')
            ->get();
    }

    /**
     * @param $lectureId
     * @param $grades
     * @return Collection
     */
    public function getLectureHomework($lectureId, $grades){
        $result = new Collection();
        if($grades){
            $gradeStudents = (new GradeUserDao())->getGradeUserWhereInGrades($grades);
            $studentsIds = [];
            foreach ($gradeStudents as $gradeStudent) {
                /**
                 * @var GradeUser $gradeStudent
                 */
                if($gradeStudent->isStudent()){
                    $studentsIds[] = $gradeStudent->user_id;
                }
            }
            $yearAndTerm = GradeAndYearUtil::GetYearAndTerm(Carbon::now());
            $result = Homework::where('year', $yearAndTerm['year'])
                ->where('lecture_id',$lectureId)
                ->whereIn('student_id',$studentsIds)
                ->orderBy('id','desc')
                ->get();
        }
        return $result;
    }

    /**
     * 学生获取自己某节课的作业
     * @param $studentId
     * @param $courseId
     * @param $idx
     * @param $year
     * @return Collection
     */
    public function getHomeworkByStudentAndLectureAndYear($studentId, $courseId, $idx, $year){
        return Homework::where('year', $year)
            ->where('course_id',$courseId)
            ->where('idx',$idx)
            ->where('student_id',$studentId)
            ->orderBy('id','desc')
            ->get();
    }

    /**
     * @param $data
     * @return Homework
     */
    public function saveHomework($data){
        return Homework::create($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteHomework($id){
        $homework = Homework::find($id);
        $filePath = $homework->url;
        if($filePath){
            $file = str_replace(env('APP_URL').'/storage','',$filePath);
            unlink(storage_path('app/public').$file);
        }
        return $homework->delete();
    }

    public function getLectureMaterial($materialId){
        return LectureMaterial::find($materialId);
    }

    /**
     * 更新课件的记录，注意这个方法只会更新title和summary这两个字段
     * @param $data
     * @return mixed
     */
    public function updateLectureSummary($data){
        return Lecture::where('id',$data['id'])->update($data);
    }

    /**
     * 保存某个课节的附件材料
     * @param $data
     * @return MessageBag
     */
    public function saveLectureMaterial($data){
        $bag = new MessageBag();
        try{
            if(empty($data['id'])){
                $material =  LectureMaterial::create($data);
                $bag->setData($material);
            }
            else{
                LectureMaterial::where('id',$data['id'])
                    ->update($data);
                $material =  LectureMaterial::find($data['id']);
                $bag->setData($material);
            }
        }catch (\Exception $exception){
            $bag->setCode(JsonBuilder::CODE_ERROR);
            $bag->setMessage($exception->getMessage());
        }
        return $bag;
    }


    /**
     * 获取学习资料的类型
     * @param $schoolId
     * @return mixed
     */
    public function getMaterialType($schoolId) {
        $map = ['school_id'=>$schoolId];
        $field = ['id as type_id', 'name'];
        return LectureMaterialType::where($map)->select($field)->get();
    }


    /**
     * @param $courseId
     * @param $gradeId
     * @param $teacherId
     * @param $type
     * @param $keyword
     * @return mixed
     */
    public function getMaterialsByType($courseId, $gradeId, $teacherId, $type, $keyword=null){
        $map = ['course_id'=>$courseId, 'grade_id'=>$gradeId,
            'teacher_id'=>$teacherId, 'type'=>$type];
        $result = LectureMaterial::where($map);
        if(!is_null($keyword)) {

            $result = $result->where('description', 'like', '%'.$keyword.'%');
        }
        return $result->get();
    }


    /**
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialByGradeId($gradeId) {
        $map = ['grade_id'=>$gradeId];
        return LectureMaterial::where($map)
            ->orderBy('created_at', 'desc')
            ->first();
    }


    /**
     * @param $courseIds
     * @param $keyword
     * @return mixed
     */
    public function getMaterialByKeyword($courseIds, $keyword) {
        return LectureMaterial::where('description', 'like', '%'.$keyword.'%')
            ->whereIn('course_id', $courseIds)
            ->get();
    }


    /**
     * 获取课程列表
     * @param $teacherId
     * @return mixed
     */
    public function getMaterialByTeacherId($teacherId) {
        return LectureMaterial::where('teacher_id', $teacherId)
            ->groupBy('course_id')
            ->select('course_id')
            ->get();
    }


    /**
     * 根据课程查询学习资料
     * @param $courseId
     * @param $type
     * @param $teacherId
     * @param bool $isPage
     * @return mixed
     */
    public function getMaterialByCourseId($courseId, $type, $teacherId, $isPage = true) {
        $map = ['course_id'=>$courseId, 'type'=>$type, 'teacher_id'=>$teacherId];
        $result = LectureMaterial::where($map);

        if($isPage) {
            return $result->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
        }

        return $result->get();
    }

    /**
     * 删除学习资料
     * @param User $user
     * @param $materialId
     * @return MessageBag
     */
    public function deleteMaterial(User $user,$materialId) {
        $messageBag = new MessageBag();
        $info = LectureMaterial::where('id', $materialId)->first();
        if(is_null($info)) {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('该资料不存在');
            return $messageBag;
        }
        if($info->teacher_id != $user->id) {
            $messageBag->setMessage('您没有权限删除');
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            return $messageBag;
        }
        $re = LectureMaterial::where('id', $materialId)->delete();
        if($re) {
            $messageBag->setMessage('删除成功');
        } else {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('删除失败');
        }
        return $messageBag;
    }


    /**
     * 获取数量
     * @param $teacherId
     * @param $type
     * @return mixed
     */
    public function getMaterialNumByUserAndType($teacherId, $type) {
        $map = ['teacher_id'=>$teacherId, 'type'=>$type];
        return LectureMaterial::where($map)->count();
    }


    /**
     * 上传学习资料
     * @param $data
     * @return MessageBag
     */
    public function addMaterial($data) {
        $messageBag = new MessageBag();

        // 查询当前课节是否已上传
        $info = $this->getMaterialByCourseIdAndIdxAndTeacherId($data['course_id'], $data['idx'], $data['user_id']);

        $lecture = [
            'course_id' => $data['course_id'],
            'teacher_id' => $data['user_id'],
            'idx' => $data['idx'],
            'title' => $data['title'],
        ];

        try{
            DB::beginTransaction();

            if(is_null($info)) {
                $info = Lecture::create($lecture);
            }

            foreach ($data['material'] as $key => $item) {
                $material = [
                    'lecture_id' => $info->id,
                    'teacher_id' => $data['user_id'],
                    'course_id' => $data['course_id'],
                    'media_id' => $item['media_id']??0,
                    'type' => $item['type_id'],
                    'description' => $item['desc'],
                    'url' => $item['url'],
                    'grade_id' => $data['grade_id'],
                    'idx' => $data['idx'],
                ];

                LectureMaterial::create($material);
            }
            DB::commit();
            $messageBag->setMessage('上传成功');

        } catch (\Exception $e) {

            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage($msg);
        }

        return $messageBag;
    }


    /**
     * 根据课节查询
     * @param $courseId
     * @param $idx
     * @param $teacherId
     * @return mixed
     */
    public function getMaterialByCourseIdAndIdxAndTeacherId($courseId, $idx, $teacherId) {
        $map = ['idx'=>$idx, 'course_id'=>$courseId, 'teacher_id'=>$teacherId];
        return Lecture::where($map)->first();
    }


    /**
     * 根据老师获取学习资料
     * @param $userId
     * @return mixed
     */
    public function getMaterialByUser($userId) {
        $map = ['teacher_id'=>$userId];
        return LectureMaterial::where($map)
            ->select(['lecture_id', 'media_id'])
            ->distinct(['lecture_id', 'media_id'])
            ->get();
    }


    /**
     * 根据课节和资料获取信息
     * @param $lectureId
     * @param $mediaId
     * @return mixed
     */
    public function getMaterialByLectureIdAndMediaId($lectureId, $mediaId) {
        $map = ['lecture_id'=>$lectureId, 'media_id'=>$mediaId];
        return LectureMaterial::where($map)->get();
    }


    /**
     * @param $courseId
     * @param $teacherId
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialsByCourseIdAndTeacherIdAndGradeId($courseId, $teacherId, $gradeId) {
        $map = [
            'course_id'=>$courseId, 'teacher_id'=>$teacherId,
            'grade_id'=>$gradeId
        ];
        return LectureMaterial::where($map)
            ->orderBy('idx', 'asc')
            ->get();
    }


    /**
     * 查询分类
     * @param $courseId
     * @param $teacherId
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialTypeByCourseId($courseId, $teacherId, $gradeId) {
        $map = [
            'course_id'=>$courseId, 'teacher_id'=>$teacherId,
            'grade_id'=>$gradeId
        ];
        return LectureMaterial::where($map)
            ->select('type')
            ->distinct('type')
            ->get();
    }
}