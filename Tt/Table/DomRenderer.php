<?php

namespace Tt\Table;

use Dom\Builder;
use Dom\Form\Select;
use Dom\Renderer\RendererInterface;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Tk\Log;
use Tk\ObjectUtil;
use Tt\Table;

class DomRenderer extends TableRenderer implements RendererInterface
{
    use RendererTrait;

    protected array $params = [];
    protected Builder $builder;


    public function __construct(Table $table, array $rows, string $templatePath)
    {
        parent::__construct($table, $rows, $templatePath);
        $this->init($this->path);
    }

    protected function init(string $tplFile): void
    {
        $this->builder = new Builder($tplFile);

        // get any data-opt options from the template and remove them
        $tableEl = $this->builder->getDocument()->getElementById('tpl-table');
        $cssPre = 'data-opt-';
        /** @var \DOMAttr $attr */
        foreach ($tableEl->attributes as $attr) {
            if (str_starts_with($attr->name, $cssPre)) {
                $name = str_replace($cssPre, '', $attr->name);
                $this->params[$name] = $attr->value;
            }
        }
        // Remove option attributes
        foreach ($this->params as $k => $v) {
            $tableEl->removeAttribute($cssPre . $k);
        }

        // load any cell templates
        foreach ($this->getTable()->getCells() as $cell) {
            $tpl = $this->buildTemplate('tpl-cell-' . lcfirst(ObjectUtil::basename($cell)));
            if ($tpl) {
                Log::warning('Loading table cell template: ' . 'tpl-cell-' . lcfirst(ObjectUtil::basename($cell)));
                $cell->setTemplate($tpl);
            }
        }

        $this->setTemplate($this->buildTemplate('table'));
    }

    public function buildTemplate(string $type): ?Template
    {
        return $this->builder->getTemplate('tpl-' . $type);
    }


    public function getHtml(): string
    {
        return $this->show()->toString();
    }

    function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        // Render table header elements
        $template->setAttr('thr', $this->getTable()->getHeaderAttrs()->getAttrList());
        $template->addCss('thr', $this->getTable()->getHeaderAttrs()->getCssList());
        /** @var Cell $cell */
        foreach ($this->getTable()->getCells() as $cell) {
            $cell->getHeaderAttrs()->addCss('table-light');    // bs5 style
            $th = $template->getRepeat('th');
            $th->setAttr('th', $cell->getHeaderAttrs()->getAttrList());
            $th->addCss('th', $cell->getHeaderAttrs()->getCssList());

            if ($cell->isSortable()) {
                // todo mm: get the orderBy URL from the cell?
                $orderUrl = '#';
                $th->setAttr('a', 'href', $orderUrl);
                $th->setText('a', e($cell->getHeader()));
            } else {
                $th->setText('th', e($cell->getHeader()));
            }
            $th->appendRepeat();
        }

        // Render table rows
        $rowAttrs = clone $this->getTable()->getRowAttrs();
        foreach ($this->rows as $row) {
            $tr = $template->getRepeat('tr');
            foreach ($this->getTable()->getCells() as $cell) {
                $td = $tr->getRepeat('td');
                $td->setHtml('td', $cell->getValue($row));
                $td->setAttr('td', $cell->getAttrList());
                $td->addCss('td', $cell->getCssList());
                $td->appendRepeat();
            }

            $tr->setAttr('tr', $this->getTable()->getRowAttrs()->getAttrList());
            $tr->addCss('tr', $this->getTable()->getRowAttrs()->getCssList());
            $tr->appendRepeat();

            $this->getTable()->setRowAttrs(clone $rowAttrs);
        }

        $template->setAttr('table', $this->getTable()->getAttrList());
        $template->addCss('table', $this->getTable()->getCssList());


        if ($this->isFooterEnabled()) {

            $this->showResults($template);
            $this->showPager($template);
            $this->showLimit($template);

            $template->setVisible('footer', $this->isFooterEnabled());
        }

        return $template;
    }

    protected function showResults(Template $template): void
    {
        $total = max(count($this->rows), $this->getTable()->getTotalRows());
        if (!$total) return;

        $from = $this->getTable()->getOffset() + 1;
        $to = $this->getTable()->getOffset() + $this->getTable()->getLimit();
        if ($to > $total || $to == 0) {
            $to = $total;
        }

        $template->setText('results-from', $from);
        $template->setText('results-to', $to);
        $template->setText('results-total', $total);

        $template->setVisible('results-wrap');
    }

    protected function showPager(Template $template): void
    {
        $total = max(count($this->rows), $this->getTable()->getTotalRows());
        if (!$total) return;
        if ($this->getTable()->getLimit() == 0 || $total < $this->getTable()->getLimit()) return;

        $limit = $this->getTable()->getLimit();
        $page = $this->getTable()->getPage();
        $numPages = ceil($total / $limit);
        if ($numPages < 2) return;

        $startPage = 1;
        $endPage = self::MAX_PAGES;
        $center = floor(self::MAX_PAGES / 2);

        if ($page > $center) {
            $startPage = $page - $center;
            $endPage = $startPage + self::MAX_PAGES;
        }

        if ($startPage > $numPages - self::MAX_PAGES) {
            $startPage = $numPages - self::MAX_PAGES;
            $endPage = $numPages;
        }

        if ($startPage < 1) {
            $startPage = 1;
        }
        if ($endPage >= $numPages) {
            $endPage = $numPages;
        }

        $pageUrl = \Tk\Uri::create();
        $pageKey = $this->getTable()->makeInstanceKey(Table::PARAM_PAGE);
        $pageUrl->remove($pageKey);

        for ($i = $startPage; $i <= $endPage; $i++) {
            $repeat = $template->getRepeat('page');
            $repeat->setText('pageUrl', $i);
            $repeat->setAttr('pageUrl', 'title', 'Page ' . ($i));
            $pageUrl->set($pageKey, $i);
            $repeat->setAttr('pageUrl', 'href', $pageUrl->toString());
            if ($i == $page) {
                $repeat->addCss('page', self::CSS_SELECTED);
                $repeat->setAttr('pageUrl', 'title', 'Current Page ' . ($i));
            }
            $repeat->appendRepeat();
        }

        if ($page > 1) {
            $pageUrl->set($pageKey, $page-1);
            $template->setAttr('backUrl', 'href', $pageUrl->toString());
            $template->setAttr('backUrl', 'title', 'Previous Page');
            $pageUrl->set($pageKey, 1);
            $template->setAttr('startUrl', 'href', $pageUrl->toString());
            $template->setAttr('startUrl', 'title', 'Start Page');
        } else {
            $template->addCss('start', self::CSS_DISABLED);
            $template->addCss('back', self::CSS_DISABLED);
        }

        if ($page < $endPage) {
            $pageUrl->set($pageKey, $page+1);
            $template->setAttr('nextUrl', 'href', $pageUrl->toString());
            $template->setAttr('nextUrl', 'title', 'Next Page');
            $pageUrl->set($pageKey, $numPages);
            $template->setAttr('endUrl', 'href', $pageUrl->toString());
            $template->setAttr('endUrl', 'title', 'Last Page');
        } else {
            $template->addCss('end', self::CSS_DISABLED);
            $template->addCss('next', self::CSS_DISABLED);
        }

        $template->setVisible('pager-wrap');
    }

    protected function showLimit(Template $template): void
    {
        $total = max(count($this->rows), $this->getTable()->getTotalRows());
        if (!$total) return;

        $form = $template->getForm();

        $select = $form->getFormElement('limit');
        if (!($select instanceof Select)) return;

        foreach(self::LIMIT_LIST as $k => $v) {
            $select->appendOption($k, $v);
        }

        $select->setValue($this->getTable()->getLimit());
        $select->setAttribute('data-name', $this->getTable()->makeInstanceKey(Table::PARAM_LIMIT));
        $select->setAttribute('data-page', $this->getTable()->makeInstanceKey(Table::PARAM_PAGE));
        $select->setAttribute('data-total', $total);
        $select->setAttribute('name', null);

        $js = <<<JS
jQuery(function($) {
    // Table limit on-change event
    $('.tk-limit select').change(function(e) {
        if ($(this).val() == 0 && $(this).data('total') > 1000) {
            if (!confirm('WARNING: There are large number of records, page load time may be slowed.')) return false;
        }
        const searchParams = new URLSearchParams(location.search);
        searchParams.set($(this).data('name'), $(this).val());
        searchParams.delete($(this).data('page'));
        location.search = searchParams.toString();
        return false;
    });
});
JS;
        $template->appendJs($js);

        $template->setVisible('limit-wrap');
    }

}