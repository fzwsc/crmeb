<?php
/**
 * @author: zhypy<214681832@qq.com>
 * @day: 2020/7/4
 */
declare (strict_types=1);

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemBank;

/**
 *
 * Class SystemUserLevelDao
 * @package app\dao\system
 */
class SystemBankDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemBank::class;
    }
    /**
     * 获取数据
     * @param array $where
     * @return int
     */
    public function getBank()
    {
        return $this->getOne(['is_default' => 1, 'status' => 1]);
    }
}
