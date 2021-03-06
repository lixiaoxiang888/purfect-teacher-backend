<?php


namespace App\Http\Controllers\Api\Account;

use App\Dao\Account\AccountCoreDao;
use App\Dao\Account\AccountCoreMessageDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AccountRequest;
use App\Models\Account\AccountCore;
use App\Utils\JsonBuilder;

class IndexController extends Controller
{

    /**
     * 充值中心
     * @param AccountRequest $request
     * @return string
     */
    public function index(AccountRequest $request)
    {
        $school = $request->getAppSchool();

        $user = $request->user();

        $mesDao  = new AccountCoreMessageDao;
        $message = $mesDao->getCoreMassageBySchoolId($school->id);
        if ($message) {
            $content = $message->content;
        } else {
            $content = "";
        }

        $coreDao       = new AccountCoreDao;
        $rechargeMoney = $coreDao->getAccountCoreMoneyBySchoolId($school->id);

        $data = [
            'account_money' => $user->profile->account_money,
            'red_envelope'  => $user->profile->red_envelope,
            'message'       => $content,
            'recharge'      => $rechargeMoney
        ];

        return JsonBuilder::Success($data, '充值中心');
    }


    public function addOrder(AccountRequest $request)
    {
         $id = $request->get('id');

    }



}
