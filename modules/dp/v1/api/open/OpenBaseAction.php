<?php

namespace modules\bee_invasion\v1\api\open;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\model\user\User;


abstract class OpenBaseAction extends ActionBase
{

    public    $requestMethods = ['POST'];
    public    $dataSource     = 'JSON_STRING';
    /**
     * @var $partner PlatSrcDao
     */
    protected $partner;

    public function init()
    {
        parent::init();
        Sys::app()->setDebug(false);
        $is_deubg     = $this->inputDataBox->tryGetString('is_debug') === 'kldebug';
        $partner_code = $this->inputDataBox->getStringNotNull('partner_code');
        $sign         = $this->inputDataBox->getStringNotNull('sign');
        $now_ts       = time();

        if ($is_deubg)
        {
            Sys::app()->setDebug(true);
            if ($this->inputDataBox->tryGetString('make_sign') === 'yes')
            {
                $data = $this->inputDataBox->getData();
                unset($data['sign']);
                unset($data['timestamp']);
                unset($data['s']);
                unset($data['is_debug']);
                unset($data['make_sign']);
                unset($data['partner_code']);


                $json          = json_encode($data, JSON_UNESCAPED_UNICODE);
                $this->partner = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code], false);
                if (!empty($this->partner))
                {
                    $pri_key = $this->partner->pri_key;
                    $sign    = RSA::sign("{$json}{$this->partner->src_code}{$now_ts}", $pri_key);
                    $sign    = urlencode($sign);
                    die("\n&timestamp={$now_ts}&sign={$sign}\n{$json}\n\n\ntimestamp:\n{$now_ts}\n\npartner_code:\n{$partner_code}\n\npost body:\n{$json}\n\n待签名字符串:\n{$json}{$this->partner->src_code}{$now_ts}\n\n urlencode 后的 sign:\n{$sign}\n\npost请求的 uri:\npartner_code={$partner_code}&timestamp={$now_ts}&sign={$sign}\n\n");
                }
            }

        }

        $timestamp = $this->inputDataBox->getIntNotNull('timestamp');
        if ($timestamp < ($now_ts - 300) || $timestamp > ($now_ts + 300))
        {
            throw new AdvError(AdvError::request_timeout);
        }


        $this->partner = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code], false);
        if (empty($this->partner))
        {
            throw new AdvError(AdvError::partner_not_exist);
        }

        $partner_pub_key = $this->partner->src_pub_key;
        $res             = false;
        try
        {
            $res = RSA::verify("{$this->rawPostData}{$this->partner->src_code}{$timestamp}", $sign, $partner_pub_key);
        } catch (\Exception $e)
        {
            throw new AdvError(AdvError::data_info_unexpected, '请联系开发人员，可能是上传公钥的问题', [$this->partner]);
        }
        if (empty($res))
        {
            throw new AdvError(AdvError::request_sign_error);
        }


    }
}