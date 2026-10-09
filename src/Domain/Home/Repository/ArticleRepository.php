<?php

declare(strict_types=1);

namespace App\Domain\Home\Repository;

use App\Domain\Home\Exception\ArticlesLoadException;
use App\Domain\Home\Model\Article;

interface ArticleRepository
{
    /**
     * @return list<Article>
     *
     * @throws ArticlesLoadException
     */
    public function findLatest(int $limit): array;
}
