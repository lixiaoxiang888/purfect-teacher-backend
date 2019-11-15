<?php


namespace App\Http\Requests\NetworkDisk;


use Ramsey\Uuid\Uuid;
use App\Dao\NetworkDisk\CategoryDao;
use App\Http\Requests\MyStandardRequest;

class MediaRequest extends MyStandardRequest
{

    /**
     * 获取uuid
     * @return mixed
     */
    public function getUuId() {
        return $this->get('uuid',null);
    }


    /**
     * 获取Category的uuid
     * @return mixed
     */
    public function getCategory() {
        return $this->get('category',null);
    }



    /**
     * 获取上传文件
     * @return array|\Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|null
     */
    public function getFile() {
        return $this->file('file',null);
    }


    /**
     * 文件的关键字, 用于查询
     * @return mixed
     */
    public function getKeywords() {
        $keywords = $this->get('keywords',null);
        return !is_null($keywords)?$keywords:$this->getFile()->getClientOriginalName();
    }


    /**
     * 文件的描述文字
     * @return mixed
     */
    public function getDescription() {
        return $this->get('description',null);
    }




    /**
     * 获取上传数据
     * @return array
     * @throws \Exception
     */
    public function getUpload() {
        $file = $this->getFile();
        $type = $file->extension();  //文件后缀
        $categoryDao = new CategoryDao();
        $category = $categoryDao->getCateInfoByUuId($this->getCategory());
        $data = [
            'category_id' => $category->id,
            'user_id'     => $this->user()->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'keywords'    => $this->getKeywords(),
            'description' => $this->getDescription(),
            'file_name'   => $file->getClientOriginalName(),
            'size'        => $file->getSize(),
            'url'         => $file->store('public'),
            'driver'      => 1,
        ];
        return $data;
    }
}
