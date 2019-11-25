<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 25/11/19
 * Time: 11:39 PM
 */

namespace App\Dao\Schools;

use App\Models\Schools\Organization;
use Illuminate\Support\Collection;

class OrganizationDao
{
    public function __construct()
    {
    }

    /**
     * @param $schoolId
     * @return Collection
     */
    public function getBySchoolId($schoolId){
        return Organization::where('school_id',$schoolId)->orderBy('level','asc')->get();
    }

    /**
     * 获取指定级别的所有机构
     * @param $level
     * @param $schoolId
     * @return Collection
     */
    public function loadByLevel($level, $schoolId){
        return Organization::where('school_id',$schoolId)
            ->where('level',$level)
            ->get();
    }

    /**
     * 创建一个组织
     * @param $data
     * @return Organization
     */
    public function create($data){
        return Organization::create($data);
    }

    /**
     * @param $schoolId
     * @return Organization
     */
    public function getRoot($schoolId){
         $root = Organization::where('school_id',$schoolId)
            ->where('level',Organization::ROOT)
            ->where('parent_id',0)
            ->first();
         if(!$root){
            $dao = (new SchoolDao())->createRootOrganization($schoolId);
         }
         return $root;
    }

    /**
     * @param $id
     * @return Organization
     */
    public function getById($id){
        return Organization::find($id);
    }

    /**
     * 获取学校组织机构的最大级别
     * @param $schoolId
     * @return int
     */
    public function getTotalLevel($schoolId){
        $org = Organization::where('school_id',$schoolId)->orderBy('level','desc')->first();
        return $org->level??0;
    }

    public function output(Organization $org){
        foreach ($org->branch as $branch){
            $branch->output();
            $this->output($branch);
        }
    }
}