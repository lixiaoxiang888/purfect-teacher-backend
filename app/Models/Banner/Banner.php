<?php
namespace App\Models\Banner;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'school_id','app', 'posit', 'type', 'sort', 'title', 'image_url',
        'content', 'external','status','public','clicks','status'
    ];

    // 展示位置
    const POSIT_HOME  = 0;
    const POSIT_1  = 1;
    const POSIT_2  = 2;
    const POSIT_3  = 3;
    const POSIT_4  = 4;
    const POSIT_5  = 5;
    const POSIT_6  = 6;
    const POSIT_7  = 7;
    const POSIT_8  = 8;
    const POSIT_9  = 9;
    const POSIT_10  = 10;
    const POSIT_11  = 11;
    const POSIT_12  = 12;
    const POSIT_13  = 13;

    const POSIT_HOME_TEXT = '首页';
    const POSIT_TEXT_1 = '招生资源位';

    // 展示类型ID
    const TYPE_NO_ROTATION = 0; // 无跳转
    const TYPE_IMAGE_WRITING = 1; // 图文
    const TYPE_URL = 2; // URL
    const TYPE_NO_ROTATION_TEXT = '无跳转'; // 无跳转
    const TYPE_IMAGE_WRITING_TEXT = '图文'; // 图文
    const TYPE_URL_TEXT = 'URL'; // URL

    const STATUS_OPEN  = 1;
    const STATUS_CLOSE = 0;
    const STATUS_OPEN_TEXT = '显示';
    const STATUS_CLOSE_TEXT = '不显示';

    public $casts = [
        'public'=>'boolean','status'=>'boolean'
    ];

    /**
     * 终端
     * @var array
     */
    public static $appArr = [
      1 => '学生端',
      2 => '老师端',
    ];

    /**
     * 位置
     * @var array
     */
    public static $positArr = [
      11 => '校园首页',// 学生端-校园首页
      21 => '校园首页',// 老师端-校园首页
    ];

    /**
     * 类型
     * @var array
     */
    public static $typeArr = [
        1 => '无跳转',
        2 => '图文', // content
        3 => 'URL', // external
        4 => '校园网',
        5 => '招生主页',
        6 => '迎新主页',
        7 => '选课列表页',
        8 => '社区动态详情', // external
        11 => '无跳转',
        12 => '图文', // content
        13 => 'URL', // external
    ];

    /**
     * Func 学校对应的资源位
     * @return array
     */
    public function getAppArr()
    {
      return self::$appArr;
    }

    /**
     * Func 学校对应的资源位
     * @param int $appid (1:学生端，2:老师端)
     * @return array
     */
    public function getPositArr($appid = 0)
    {
      if (!$appid) return [];
      $positArr = [
        // 学生端
        1 => array_intersect_key(self::$positArr, array_flip([11])),
        // 教师端
        2 => array_intersect_key(self::$positArr, array_flip([21])),
      ];
      return $positArr[$appid];
    }

    /**
     * Func 获取位置属性
     *
     * @param int $posit
     * @return array
     */
    public function getTypeArr($posit = 0)
    {
      if (!$posit) return [];
      $positArr = [
        // 学生端校园首页
        11 => array_intersect_key(self::$typeArr, array_flip([1, 2, 3, 4, 5, 6, 7, 8])),
        // 教师端校园首页
        21 => array_intersect_key(self::$typeArr, array_flip([11, 12, 13])),
      ];
      return $positArr[$posit];
    }

    /**
     * Func 学校对应的资源位
     * @param int $app 终端id
     * @return String
     */
    public function getAppStr($app = 0)
    {
      return $app ? self::$appArr[$app] : '';
    }

    /**
     * Func 位置名称
     * @param int $posit 位置id
     * @return String
     */
    public function getPositStr($posit = 0)
    {
      return $posit ? self::$positArr[$posit] : '';
    }

    /**
     * Func 类型名称
     * @param int $type 类型id
     * @return String
     */
    public function getTypeStr($type = 0)
    {
      return $type ? self::$typeArr[$type] : '';
    }
  //------------------------------------------------下面是以前的-----------------------------------------------
    /**
     * Func 获取类型
     *
     * @param int $typeid
     * @return array|string
     */
    public static function allType($typeid = 0)
    {
        if ($typeid) {
            return empty(self::$typeArr[$typeid]) ? self::$typeArr[$typeid] : '';
        }
        return self::$typeArr;
    }

    public function isPublicText(){
        return $this->public ? '不需登录' : '需要登录';
    }

    public function getImageUrlAttribute($value){;
       return asset($value);
    }
}
