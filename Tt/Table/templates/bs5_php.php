<?php

/** @var array $rows */
/** @var \Tt\Table\Cell $cell */

/** @var \Tt\Table $table */
$table = $this->table;

/** @var \Tt\Table\PhpRenderer $renderer */
$renderer = $this;

$rowAttrs = clone $table->getRowAttrs();

$total = max(count($rows), $table->getTotalRows());
$from = $table->getOffset() + 1;
$to = $table->getOffset() + $table->getLimit();
if ($to > $total || $to == 0) {
    $to = $total;
}
?>
<div class="tk-table" var="tk-table" id="tpl-table">

    <div class="tk-filters" choice="filters"></div>

    <div class="tk-actions" choice="actions"></div>

    <div class="tk-table-wrapper table-responsive">
        <table class="tk-table table table-bordered table-hover <?= $table->getCssString() ?>" <?= $table->getAttrString() ?>>
            <thead class="table-light">
            <tr>
                <? foreach ($table->getCells() as $cell): ?>
                    <th <?= $cell->getHeaderAttrs()->getAttrString(true) ?>>
                        <? if ($cell->isSortable()): ?>
                            <?
                            // todo mm: get the orderBy URL from the cell?
                            $orderUrl = '#';
                            ?>
                            <a class="noblock" href="<?= $orderUrl ?>"><?= e($cell->getHeader()) ?></a>
                        <? else: ?>
                            <?= e($cell->getHeader()) ?>
                        <? endif ?>
                    </th>
                <? endforeach ?>
            </tr>
            </thead>
            <tbody>
            <? foreach ($rows as $row): ?>
                <?
                // get cell values so we can capture the row attrs
                $vals = [];
                foreach ($table->getCells() as $cell) {
                    $vals[$cell->getName()] = $cell->getValue($row);
                }
                ?>
                <tr <?= $table->getRowAttrs()->getAttrString(true) ?>>
                    <? foreach ($table->getCells() as $cell): ?>
                        <td <?= $cell->getAttrString(true) ?>>
                            <?= $vals[$cell->getName()] ?>
                        </td>
                    <? endforeach ?>
                </tr>
                <? $table->setRowAttrs(clone $rowAttrs); ?>
            <? endforeach ?>
            </tbody>
        </table>
    </div>

    <? if($renderer->isFooterEnabled()): ?>
        <div class="tk-foot row">

            <div class="tk-results col-4">
                <? if(count($rows)): ?>
                    <small>
                        <span><?= $from ?></span>-<span><?= $to ?></span> of <span><?= $total ?></span> rows
                    </small>
                <? endif ?>
            </div>
            <nav class="tk-pager paging_simple_numbers col-4">
                <ul class="pagination pagination-sm pagination-rounded" choice="pager-wrap">
                    <li class="paginate_button page-item" var="start"><a class="page-link" href="javascript:;" var="startUrl" rel="nofollow">&lt;&lt;</a></li>
                    <li class="paginate_button page-item" var="back"><a class="page-link" href="javascript:;" var="backUrl">&lt;</a></li>
                    <li class="paginate_button page-item" repeat="page" var="page"><a class="page-link" href="javascript:;" var="pageUrl" rel="nofollow"></a></li>
                    <li class="paginate_button page-item" var="next"><a class="page-link" href="javascript:;" var="nextUrl">&gt;</a></li>
                    <li class="paginate_button page-item" var="end"><a class="page-link" href="javascript:;" var="endUrl" rel="nofollow">&gt;&gt;</a></li>
                </ul>
            </nav>
            <div class="tk-limit col-4">
                <div class="row" choice="limit-wrap">
                    <div class="col-auto align-self-end" var="limit">
                        <select class="form-select form-select-sm" choice="select">
                            <option value="0">-- ALL --</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
    <? endif ?>
</div>