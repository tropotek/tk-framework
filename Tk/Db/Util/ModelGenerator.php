<?php
namespace Tk\Db\Util;



use Tk\Exception;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class ModelGenerator
{
    protected ?\Tk\Db\Pdo $db = null;

    protected string $table = '';

    protected string $className = '';

    protected string $namespace = '';

    protected array $tableInfo = [];


    /**
     * @throws \Exception
     */
    protected function __construct(\Tk\Db\Pdo $db, string $table, string $namespace = 'App', string $className = '')
    {
        $this->db = $db;
        $this->table = $table;
        $namespace = trim($namespace);
        if (!$namespace)
            $namespace = 'App';
        $this->setNamespace($namespace);

        $className = trim($className);
        if (!$className) {
            $className = $this->makeClassname($table);
        }
        $this->setClassName($className);

        if (!$db->hasTable($table)) {   // Check the DB for the table
            throw new \Exception('Table `' . $table . '` not found in the DB `' . $db->getDatabaseName().'`');
        }

        $this->tableInfo = $db->getTableInfo($table);
    }

    /**
     * @throws \Exception
     */
    public static function create(\Tk\Db\Pdo $db, string $table, string $namespace = 'App', string $className = ''): ModelGenerator
    {
        return new static($db, $table, $namespace, $className);
    }

    protected function makeClassname(string $table): string
    {
        $classname = preg_replace_callback('/_([a-z])/i', function ($matches) {
            return strtoupper($matches[1]);
        }, $table);
        return ucfirst($classname);
    }

    protected function getDefaultData(): array
    {
        $now = \Tk\Date::create();
        $a = [
            'author-name' => 'Tropotek',
            'author-biz' => 'Tropotek',
            'author-www' => 'http://tropotek.com.au/',
            'date' => $now->format(\Tk\Date::FORMAT_ISO_DATE),
            'year' => $now->format('Y'),
            'classname' => $this->getClassName(),
            'name' => trim(preg_replace('/[A-Z]/', ' $0', $this->getClassName())) ,
            'table' => $this->getTable(),
            'namespace' => $this->getNamespace(),
            'db-namespace' => $this->getDbNamespace(),
            'table-namespace' => $this->getTableNamespace(),
            'form-namespace' => $this->getFormNamespace(),
            'controller-namespace' => $this->getControllerNamespace(),
            'property-name' => lcfirst($this->getClassName()),
            'namespace-url' => str_replace('_', '/', $this->getTable()),
            'table-id' => str_replace('_', '-', $this->getTable())
        ];
        return $a;
    }

    public function setNamespace(string $namespace): static
    {
        $this->namespace = trim($namespace, '\\');
        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDbNamespace(): string
    {
        return $this->namespace . '\Db';
    }

    public function getTableNamespace(): string
    {
        return $this->namespace . '\Table';
    }

    public function getFormNamespace(): string
    {
        return $this->namespace . '\Form';
    }

    public function getControllerNamespace(): string
    {
        return $this->namespace . '\Controller';
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setClassName(string $className): static
    {
        $this->className = trim($className, '\\');
        return $this;
    }

    protected function tableFromClass(): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->getClassName())), '_');
    }

    protected function arrayMerge(array $data, array $classData = [], array $params = []): array
    {
        unset($params['namespace']);
        unset($params['classname']);
        unset($params['basepath']);
        return array_merge($data, $classData, $params);
    }

    /**
     * @throws Exception
     */
    public function makeModel(array $params = []): string
    {
        $tpl = $this->createModelTemplate();
        $data = $this->arrayMerge($this->getDefaultData(), $this->processModel(), $params);
        return $tpl->parse($data);
    }

    protected function processModel(): array
    {
        $data = [
            'properties' => '',
            'construct' => '',
            'validators' => '',
            'accessors' => '',
        ];
        foreach ($this->tableInfo as $col) {
            $mp = ModelProperty::create($col);
            if ($mp->getName() == 'del') continue;
            $data['properties'] .= "\n" . $mp->getDefinition() . "\n";

            if ($mp->getName() != 'id')
                $data['accessors'] .= "\n" . $mp->getMutator($this->getClassName()) . "\n\n" . $mp->getAccessor() . "\n";

            if ($mp->getType() == '\DateTime' && $mp->get('Null') == 'NO')
                $data['construct'] .= $mp->getInitaliser() . "\n";
            if (
                $mp->get('Null') == 'NO' &&
                $mp->get('Type') != 'text' &&
                $mp->getType() != ModelProperty::TYPE_DATE &&
                $mp->getType() != ModelProperty::TYPE_BOOL &&
                $mp->getName() != 'id' &&
                $mp->getName() != 'orderBy'
            )
                $data['validators'] .= "\n" . $mp->getValidation() . "\n";
        }
        return $data;
    }

    protected function createModelTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {db-namespace};

use Tk\Db\Mapper\Model;

/**
 * @author {author-biz} <{author-www}>
 */
class {classname} extends Model
{
{properties}


    public function __construct()
    {
{construct}
    }
    {accessors}

    public function validate(): array
    {
        \$errors = [];
{validators}
        return \$errors;
    }

}
STR;
        return \Tk\CurlyTemplate::create($classTpl);
    }


    /**
     * @throws \Exception
     */
    public function makeMapper(array $params = []): string
    {
        $tpl = $this->createMapperTemplate();
        $data = $this->arrayMerge($this->getDefaultData(), $this->processMapper(), $params);
        return $tpl->parse($data);
    }

    protected function processMapper(): array
    {
        $data = [
            'column-maps' => '',
            'form-maps' => '',
            'filter-queries' => '',
            'set-table' => ''
        ];
        foreach ($this->tableInfo as $col) {
            $mp = ModelProperty::create($col);

            $exclude = ['del'];
            if (!in_array($mp->getName(), $exclude)) {
                $data['column-maps'] .= $mp->getColumnMap() . "\n";
            }

            $exclude = ['del', 'orderBy', 'modified', 'created'];
            if (!in_array($mp->getName(), $exclude)) {
                $data['form-maps'] .= $mp->getFormMap() . "\n";
            }

            if ($mp->getType() != ModelProperty::TYPE_DATE && $mp->get('Type') != 'text') {
                $data['filter-queries'] .= $mp->getFilterQuery() . "\n";
                if ($this->getTable() != $this->tableFromClass()) {
                    $data['set-table'] = "\n" . sprintf("            \$this->setTable('%s');", $this->getTable()) . "\n";
                }
            }
        }
        return $data;
    }

    protected function createMapperTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {db-namespace};

use Tk\DataMap\DataMap;
use Tk\Db\Mapper\Filter;
use Tk\Db\Mapper\Mapper;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * @author {author-biz} <{author-www}>
 */
class {classname}Map extends Mapper
{

    public function getDbMap(): DataMap
    {
        if (!\$this->dbMap) { {set-table}
            \$this->dbMap = new DataMap();
{column-maps}
        }
        return \$this->dbMap;
    }

    public function getFormMap(): DataMap
    {
        if (!\$this->formMap) {
            \$this->formMap = new DataMap();
{form-maps}
        }
        return \$this->formMap;
    }

    /**
     * @return Result|{classname}[]
     * @throws \Exception
     */
    public function findFiltered(array|Filter \$filter, ?Tool \$tool = null): Result
    {
        return \$this->selectFromFilter(\$this->makeQuery(Filter::create(\$filter)), \$tool);
    }

    public function makeQuery(Filter \$filter): Filter
    {
        \$filter->appendFrom('%s a', \$this->quoteParameter(\$this->getTable()));

        if (!empty(\$filter['keywords'])) {
            \$kw = '%' . \$this->escapeString(\$filter['keywords']) . '%';
            \$w = '';
            //\$w .= sprintf('a.name LIKE %s OR ', \$this->quote(\$kw));
            if (is_numeric(\$filter['keywords'])) {
                \$id = (int)\$filter['keywords'];
                \$w .= sprintf('a.id = %d OR ', \$id);
            }
            if (\$w) \$filter->appendWhere('(%s) AND ', substr(\$w, 0, -3));
        }

        if (!empty(\$filter['id'])) {
            \$w = \$this->makeMultiQuery(\$filter['id'], 'a.id');
            if (\$w) \$filter->appendWhere('(%s) AND ', \$w);
        }

        if (!empty(\$filter['exclude'])) {
            \$w = \$this->makeMultiQuery(\$filter['exclude'], 'a.id', 'AND', '!=');
            if (\$w) \$filter->appendWhere('(%s) AND ', \$w);
        }
{filter-queries}

        return \$filter;
    }

}
STR;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }


    /**
     * @throws \Exception
     */
    public function makeTable(array $params = []): string
    {
        $tpl = $this->createTableTemplate();
        $data = $this->arrayMerge($this->getDefaultData(), $this->processTable(), $params);
        return $tpl->parse($data);
    }

    /**
     * @throws \Exception
     */
    public function makeManager(array $params = []): string
    {
        $tpl = $this->createTableManagerTemplate();
        $data = $this->getDefaultData();
        return $tpl->parse($data);
    }

    protected function processTable(): array
    {
        $data = [
            'cell-list' => ''
        ];
        foreach ($this->tableInfo as $col) {
            $mp = ModelProperty::create($col);
            if ($mp->getName() == 'del') continue;
            if ($mp->get('Type') != 'text')
                $data['cell-list'] .= $mp->getTableCell($this->getClassName(), $this->getNamespace()) . "\n";
        }
        return $data;
    }

    protected function createTableManagerTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {controller-namespace}\{classname};

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * Add Route to routes.php:
 *     \$routes->add('{table-id}-manager', new Routing\Route('/{namespace-url}Manager.html', ['_controller' => '{controller-namespace}\{classname}\Manager::doDefault']));
 *
 * @author {author-biz} <{author-www}>
 */
class Manager extends AdminManagerIface
{

    public function __construct()
    {
        \$this->setPageTitle('{name} Manager');
    }

    /**
     * @param Request \$request
     * @throws \Exception
     */
    public function doDefault(Request \$request)
    {
        \$this->setTable(\{table-namespace}\{classname}::create());
        \$this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/{namespace-url}Edit.html'));
        \$this->getTable()->init();

        \$filter = [];
        \$this->getTable()->setList(\$this->getTable()->findList(\$filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        \$this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New {name}',
            \$this->getTable()->getEditUrl(), 'fa fa-book fa-add-action'));
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        \$this->initActionPanel();
        \$template = parent::show();

        \$template->appendTemplate('panel', \$this->getTable()->show());

        return \$template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        \$xhtml = <<<HTML
<div class="tk-panel" data-panel-title="{name}s" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load(\$xhtml);
    }

}
STR;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }

    protected function createTableTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {table-namespace};

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   \$table = new {classname}::create();
 *   \$table->init();
 *   \$list = ObjectMap::getObjectListing();
 *   \$table->setList(\$list);
 *   \$tableTemplate = \$table->show();
 *   \$template->appendTemplate(\$tableTemplate);
 * </code>
 *
 * @author {author-biz} <{author-www}>
 */
class {classname} extends \Bs\TableIface
{

    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {

{cell-list}
        // Filters
        \$this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //\$this->appendAction(\Tk\Table\Action\Link::createLink('New {name}', \Bs\Uri::createHomeUrl('/{namespace-url}Edit.html'), 'fa fa-plus'));
        //\$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(['modified', 'created']));
        \$this->appendAction(\Tk\Table\Action\Delete::create());
        \$this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //\$this->setList(\$this->findList());

        return \$this;
    }

    /**
     * @param array \$filter
     * @param null|\Tk\Db\Tool \$tool
     * @return \Tk\Db\Map\ArrayObject|\{db-namespace}\{classname}[]
     * @throws \Exception
     */
    public function findList(\$filter = [], \$tool = null)
    {
        if (!\$tool) \$tool = \$this->getTool();
        \$filter = array_merge(\$this->getFilterValues(), \$filter);
        \$list = \{db-namespace}\{classname}Map::create()->findFiltered(\$filter, \$tool);
        return \$list;
    }

}
STR;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }

    /**
     * @throws \Exception
     */
    public function makeForm(array $params = []): string
    {
        $tpl = $this->createFormIfaceTemplate();
        if (!empty($params['modelForm']))
            $tpl = $this->createModelFormTemplate();
        $data = $this->arrayMerge($this->getDefaultData(), $this->processForm(!empty($params['modelForm'])), $params);
        return $tpl->parse($data);
    }

    /**
     * @throws \Exception
     */
    public function makeEdit(array $params = []): string
    {
        $tpl = $this->createFormEditTemplate();
        $data = $this->getDefaultData();
        return $tpl->parse($data);
    }

    protected function processForm(bool $isModelForm = false): array
    {
        $data = [
            'field-list' => ''
        ];
        foreach ($this->tableInfo as $col) {
            $mp = ModelProperty::create($col);
            if ($mp->getName() == 'del' || $mp->getName() == 'modified' || $mp->getName() == 'created' || $mp->getName() == 'id') continue;
            $data['field-list'] .= $mp->getFormField($this->getClassName(), $this->getNamespace(), $isModelForm) . "\n";
        }
        return $data;
    }

    protected function createFormEditTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {controller-namespace}\{classname};

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      \$routes->add('{table-id}-edit', Route::create('/staff/{namespace-url}Edit.html', '{controller-namespace}\{classname}\Edit::doDefault'));
 *
 * @author {author-biz} <{author-www}>
 */
class Edit extends AdminEditIface
{

    /**
     * @var \{db-namespace}\{classname}
     */
    protected \${property-name} = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        \$this->setPageTitle('{name} Edit');
    }

    /**
     * @param Request \$request
     * @throws \Exception
     */
    public function doDefault(Request \$request)
    {
        \$this->{property-name} = new \{db-namespace}\{classname}();
        if (\$request->get('{property-name}Id')) {
            \$this->{property-name} = \{db-namespace}\{classname}Map::create()->find(\$request->get('{property-name}Id'));
        }

        \$this->setForm(\{form-namespace}\{classname}::create()->setModel(\$this->{property-name}));
        \$this->initForm(\$request);
        \$this->getForm()->execute();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        \$template = parent::show();

        // Render the form
        \$template->appendTemplate('panel', \$this->getForm()->show());

        return \$template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        \$xhtml = <<<HTML
<div class="tk-panel" data-panel-title="{name} Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load(\$xhtml);
    }

}
STR;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }

    protected function createFormIfaceTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<PHP
<?php
namespace {form-namespace};

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   \$form = new {classname}::create();
 *   \$form->setModel(\$obj);
 *   \$formTemplate = \$form->getRenderer()->show();
 *   \$template->appendTemplate('form', \$formTemplate);
 * </code>
 *
 * @author {author-biz} <{author-www}>
 */
class {classname} extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

{field-list}
        \$this->appendField(new Event\Submit('update', [\$this, 'doSubmit']));
        \$this->appendField(new Event\Submit('save', [\$this, 'doSubmit']));
        \$this->appendField(new Event\Link('cancel', \$this->getBackUrl()));

    }

    /**
     * @param \Tk\Request \$request
     * @throws \Exception
     */
    public function execute(\$request = null)
    {
        \$this->load(\{db-namespace}\{classname}Map::create()->unmapForm(\$this->get{classname}()));
        parent::execute(\$request);
    }

    /**
     * @param Form \$form
     * @param Event\FieldInterface \$event
     * @throws \Exception
     */
    public function doSubmit(\$form, \$event)
    {
        // Load the object with form data
        \{db-namespace}\{classname}Map::create()->mapForm(\$form->getValues(), \$this->get{classname}());

        // Do Custom Validations

        \$form->addFieldErrors(\$this->get{classname}()->validate());
        if (\$form->hasErrors()) {
            return;
        }

        \$isNew = (bool)\$this->get{classname}()->getId();
        \$this->get{classname}()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        \$event->setRedirect(\$this->getBackUrl());
        if (\$form->getTriggeredEvent()->getName() == 'save') {
            \$event->setRedirect(\Tk\Uri::create()->set('{property-name}Id', \$this->get{classname}()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\{db-namespace}\{classname}
     */
    public function get{classname}()
    {
        return \$this->getModel();
    }

    /**
     * @param \{db-namespace}\{classname} \${property-name}
     * @return \$this
     */
    public function set{classname}(\${property-name})
    {
        return \$this->setModel(\${property-name});
    }

}
PHP;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }

    protected function createModelFormTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<STR
<?php
namespace {form-namespace};

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   \$form = new {classname}::create();
 *   \$form->setModel(\$obj);
 *   \$formTemplate = \$form->getRenderer()->show();
 *   \$template->appendTemplate('form', \$formTemplate);
 * </code>
 *
 * @author {author-biz} <{author-www}>
 */
class {classname} extends \Bs\ModelForm
{

    /**
     * @throws \Exception
     */
    public function init()
    {

{field-list}
        \$this->getForm()->appendField(new Event\Submit('update', [\$this, 'doSubmit']));
        \$this->getForm()->appendField(new Event\Submit('save', [\$this, 'doSubmit']));
        \$this->getForm()->appendField(new Event\Link('cancel', \$this->getBackUrl()));

    }

    /**
     * @param \Tk\Request \$request
     * @throws \Exception
     */
    public function execute(\$request = null)
    {
        \$this->getForm()->load(\{db-namespace}\{classname}Map::create()->unmapForm(\$this->get{classname}()));
        parent::execute(\$request);
    }

    /**
     * @param Form \$form
     * @param Event\FieldInterface \$event
     * @throws \Exception
     */
    public function doSubmit(\$form, \$event)
    {
        // Load the object with form data
        \{db-namespace}\{classname}Map::create()->mapForm(\$form->getValues(), \$this->get{classname}());

        // Do Custom Validations

        \$form->addFieldErrors(\$this->get{classname}()->validate());
        if (\$form->hasErrors()) {
            return;
        }

        \$isNew = !(bool)\$this->get{classname}()->getId();
        \$this->get{classname}()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        \$event->setRedirect(\$this->getBackUrl());
        if (\$form->getTriggeredEvent()->getName() == 'save') {
            \$event->setRedirect(\Tk\Uri::create()->set('{property-name}Id', \$this->get{classname}()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\{db-namespace}\{classname}
     */
    public function get{classname}()
    {
        return \$this->getModel();
    }

    /**
     * @param \{db-namespace}\{classname} \${property-name}
     * @return \$this
     */
    public function set{classname}(\${property-name})
    {
        return \$this->setModel(\${property-name});
    }

}
STR;
        $tpl = \Tk\CurlyTemplate::create($classTpl);
        return $tpl;
    }


}
