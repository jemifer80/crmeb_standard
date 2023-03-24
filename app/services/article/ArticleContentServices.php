<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\article;

use app\dao\article\ArticleContentDao;
use app\services\BaseServices;

/**
 * 文章内容
 * Class ArticleContentServices
 * @package app\services\article
 * @mixin ArticleContentDao
 */
class ArticleContentServices extends BaseServices
{
    /**
     * ArticleContentServices constructor.
     * @param ArticleContentDao $dao
     */
    public function __construct(ArticleContentDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        return $this->dao->del($id);
    }
}
