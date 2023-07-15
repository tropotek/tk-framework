<?php
namespace Tk\Db\Util;

use Tk\Exception;

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

    protected function makePropertyName(string $colName): string
    {
        $prop = preg_replace_callback('/_([a-z])/i', function ($matches) {
            return strtoupper($matches[1]);
        }, $colName);
        return lcfirst($prop);
    }

    protected function getDefaultData(): array
    {
        $now = \Tk\Date::create();
        $primaryKey = 'id';
        foreach ($this->tableInfo as $col => $info) {
            if (($info['Key'] ?? '') == 'PRI') {
                vd($info);
                $primaryKey = $info['Field'];
            }
        }
        return [
            'author-name' => 'Tropotek',
            'author-biz' => 'Tropotek',
            'author-www' => 'http://tropotek.com.au/',
            'date' => $now->format(\Tk\Date::FORMAT_ISO_DATE),
            'year' => $now->format('Y'),
            'classname' => $this->getClassName(),
            'name' => trim(preg_replace('/[A-Z]/', ' $0', $this->getClassName())),
            'table' => $this->getTable(),
            'namespace' => $this->getNamespace(),
            'db-namespace' => $this->getDbNamespace(),
            'table-namespace' => $this->getTableNamespace(),
            'form-namespace' => $this->getFormNamespace(),
            'controller-namespace' => $this->getControllerNamespace(),
            'property-name' => lcfirst($this->getClassName()),
            'namespace-url' => str_replace('_', '/', $this->getTable()),
            'table-id' => str_replace('_', '-', $this->getTable()),
            'primary-col' => $primaryKey,
            'primary-prop' => $this->makePropertyName($primaryKey),
        ];
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
                $data['accessors'] .= "\n" . $mp->getAccessor() . "\n\n" . $mp->getMutator($this->getClassName()) . "\n";

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
            'db-maps' => '',
            'form-maps' => '',
            'table-maps' => '',
            'filter-queries' => '',
            'set-table' => ''
        ];
        foreach ($this->tableInfo as $col) {
            $mp = ModelProperty::create($col);

            $exclude = ['del'];
            if (!in_array($mp->getName(), $exclude)) {
                $data['db-maps'] .= $mp->getColumnMap() . "\n";
            }

            $exclude = ['del', 'orderBy', 'modified', 'created'];
            if (!in_array($mp->getName(), $exclude)) {
                $data['form-maps'] .= $mp->getFormMap() . "\n";
            }

            $exclude = ['del'];
            if (!in_array($mp->getName(), $exclude)) {
                $data['table-maps'] .= $mp->getTableMap() . "\n";
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
use Tk\DataMap\Table;

class {classname}Map extends Mapper
{

    public function makeDataMaps(): void
    { {set-table}
        if (!\$this->getDataMappers()->has(self::DATA_MAP_DB)) {
            \$map = new DataMap();
{db-maps}
            \$this->addDataMap(self::DATA_MAP_DB, \$map);
        }

        if (!\$this->getDataMappers()->has(self::DATA_MAP_FORM)) {
            \$map = new DataMap();
{form-maps}
            \$this->addDataMap(self::DATA_MAP_FORM, \$map);
        }

        if (!\$this->getDataMappers()->has(self::DATA_MAP_TABLE)) {
            \$map = new DataMap();
{table-maps}
            \$this->addDataMap(self::DATA_MAP_TABLE, \$map);
        }
    }

    /**
     * @return Result|{classname}[]
     */
    public function findFiltered(array|Filter \$filter, ?Tool \$tool = null): Result
    {
        return \$this->selectFromFilter(\$this->makeQuery(Filter::create(\$filter)), \$tool);
    }

    public function makeQuery(Filter \$filter): Filter
    {
        \$filter->appendFrom('%s a', \$this->quoteParameter(\$this->getTable()));

        if (!empty(\$filter['search'])) {
            \$kw = '%' . \$this->escapeString(\$filter['search']) . '%';
            \$w = '';
            //\$w .= sprintf('a.name LIKE %s OR ', \$this->quote(\$kw));
            if (is_numeric(\$filter['search'])) {
                \$id = (int)\$filter['search'];
                \$w .= sprintf('a.{primary-col} = %d OR ', \$id);
            }
            if (\$w) \$filter->appendWhere('(%s) AND ', substr(\$w, 0, -3));
        }

        if (!empty(\$filter['id'])) {
            \$filter['{primary-prop}'] = \$filter['id'];
        }
        if (!empty(\$filter['{primary-prop}'])) {
            \$w = \$this->makeMultiQuery(\$filter['{primary-prop}'], 'a.{primary-col}');
            if (\$w) \$filter->appendWhere('(%s) AND ', \$w);
        }
{filter-queries}
        if (!empty(\$filter['exclude'])) {
            \$w = \$this->makeMultiQuery(\$filter['exclude'], 'a.{primary-col}', 'AND', '!=');
            if (\$w) \$filter->appendWhere('(%s) AND ', \$w);
        }

        return \$filter;
    }

}
STR;
        return \Tk\CurlyTemplate::create($classTpl);
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
        $classTpl = <<<PHP
<?php
namespace {controller-namespace}\{classname};

use Bs\PageController;
use Bs\Table\ManagerTrait;
use Dom\Template;
use Bs\Db\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Add Route to /src/config/routes.php:
 * ```php
 *   \$routes->add('{table-id}-manager', '/{namespace-url}Manager')
 *       ->controller([{controller-namespace}\{classname}\Manager::class, 'doDefault']);
 * ```
 */
class Manager extends PageController
{
    use ManagerTrait;

    public function __construct()
    {
        parent::__construct(\$this->getFactory()->getAdminPage());
        \$this->getPage()->setTitle('{name} Manager');
        \$this->setAccess(User::PERM_MANAGE_STAFF);
    }

    public function doDefault(Request \$request): \App\Page|\Dom\Mvc\Page
    {
        \$this->setTable(new \{table-namespace}\{classname}());
        \$this->getTable()->findList([], \$this->getTable()->getTool());
        \$this->getTable()->init()->execute(\$request);

        return \$this->getPage();
    }

    public function show(): ?Template
    {
        \$template = \$this->getTemplate();
        \$template->setText('title', \$this->getPage()->getTitle());
        \$template->setAttr('create', 'href', \$this->getBackUrl());

        \$template->appendTemplate('content', \$this->getTable()->show());

        return \$template;
    }

    public function __makeTemplate(): ?Template
    {
        \$html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="#" title="Create {name}" class="btn btn-outline-secondary" var="create"><i class="fa fa-plus"></i> Create {name}</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-cogs"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return \$this->loadTemplate(\$html);
    }

}
PHP;
        return \Tk\CurlyTemplate::create($classTpl);
    }

    protected function createTableTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<PHP
<?php
namespace {table-namespace};

use {db-namespace}\{classname}Map;
use Dom\Template;
use Bs\Table\ManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Traits\SystemTrait;
use Tk\Uri;
use Tk\Form;
use Tk\Form\Field;
use Tk\FormRenderer;
use Tk\Table;
use Tk\Table\Cell;
use Tk\Table\Action;
use Tk\TableRenderer;
use Tk\Db\Tool;

class {classname} extends ManagerInterface
{
    use SystemTrait;

    public function initCells(): void
    {
        \$editUrl = Uri::create('/{namespace-url}Edit');

{cell-list}

        // Filters
        \$this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        // Actions
        //\$this->getTable()->appendAction(new Action\Button('Create'))->setUrl(\$editUrl);
        \$this->getTable()->appendAction(new Action\Delete());
        \$this->getTable()->appendAction(new Action\Csv())->addExcluded('actions');

    }

    public function execute(Request \$request): static
    {
        parent::execute(\$request);
        return \$this;
    }

    public function findList(array \$filter = [], ?Tool \$tool = null): null|array|Result
    {
        if (!\$tool) \$tool = \$this->getTool();
        \$filter = array_merge(\$this->getFilterForm()->getFieldValues(), \$filter);
        \$list = {classname}Map::create()->findFiltered(\$filter, \$tool);
        \$this->setList(\$list);
        return \$list;
    }

    public function show(): ?Template
    {
        \$renderer = \$this->getTableRenderer();
        \$this->getRow()->addCss('text-nowrap');
        \$this->showFilterForm();
        return \$renderer->show();
    }
}
PHP;
        return \Tk\CurlyTemplate::create($classTpl);
    }

    /**
     * @throws \Exception
     */
    public function makeForm(array $params = []): string
    {
        $tpl = $this->createFormTemplate();
//        if (!empty($params['modelForm']))
//            $tpl = $this->createModelFormTemplate();
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

use {db-namespace}\{classname};
use {db-namespace}\{classname}Map;
use Bs\Form\EditTrait;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Bs\Db\User;

/**
 * Add Route to /src/config/routes.php:
 * ```php
 *   \$routes->add('{table-id}-manager', '/{namespace-url}Edit')
 *       ->controller([{controller-namespace}\{classname}\Edit::class, 'doDefault']);
 * ```
 */
class Edit extends PageController
{
    use EditTrait;

    protected ?\{db-namespace}\{classname} \${property-name} = null;


    public function __construct()
    {
        parent::__construct(\$this->getFactory()->getAdminPage());
        \$this->getPage()->setTitle('Edit {name}');
        \$this->setAccess(User::PERM_ADMIN);
    }

    public function doDefault(Request \$request)
    {
        if (\$request->get('{property-name}Id')) {
            \$this->{property-name} = \{db-namespace}\{classname}Map::create()->find(\$request->get('id'));
        }
        if (!\$this->{property-name}) {
            throw new Exception('Invalid Example ID: ' . \$request->query->getInt('exampleId'));
        }

        \$this->setForm(new \{form-namespace}\{classname}(\$this->{property-name}));
        \$this->getForm()->init()->execute(\$request->request->all());

        return \$this->getPage();
    }

    public function show(): ?Template
    {
        \$template = \$this->getTemplate();
        \$template->setText('title', \$this->getPage()->getTitle());
        \$template->setAttr('back', 'href', \$this->getBackUrl());

        \$template->appendTemplate('content', \$this->form->show());

        return \$template;
    }

    public function __makeTemplate(): ?Template
    {
        \$html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return \$this->loadTemplate(\$html);
    }

}
STR;
        return \Tk\CurlyTemplate::create($classTpl);
    }

    protected function createFormTemplate(): \Tk\CurlyTemplate
    {
        $classTpl = <<<PHP
<?php
namespace {form-namespace};

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Exception;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Action;
use Tk\FormRenderer;
use Tk\Traits\SystemTrait;
use Tk\Uri;

class {classname}
{
    use SystemTrait;
    use Form\FormTrait;

    protected ?\{db-namespace}\{classname} \${property-name} = null;


    public function __construct()
    {
        \$this->setForm(Form::create('{property-name}'));
    }

    public function doDefault(Request \$request, int \$id)
    {
        \$this->{property-name} = new \{db-namespace}\{classname}();

        if (\$id) {
            \$this->{property-name} = \{db-namespace}\{classname}Map::create()->find(\$id);
            if (!\$this->{property-name}) {
                throw new Exception('Invalid ID: ' . \$id);
            }
        }

{field-list}

        \$this->getForm()->appendField(new Action\SubmitExit('save', [\$this, 'onSubmit']));
        \$this->getForm()->appendField(new Action\Link('cancel', Uri::create('/{property-name}Manager')));

        \$load = \$this->{property-name}->getMapper()->getFormMap()->getArray(\$this->{property-name});
        \$load['id'] = \$this->{property-name}->getId();
        \$this->getForm()->setFieldValues(\$load); // Use form data mapper if loading objects

        \$this->getForm()->execute(\$request->request->all());

        \$this->setFormRenderer(new FormRenderer(\$this->getForm()));

    }

    public function onSubmit(Form \$form, Action\ActionInterface \$action)
    {
        \$this->{property-name}->getMapper()->getFormMap()->loadObject(\$this->{property-name}, \$form->getFieldValues());

        \$form->addFieldErrors(\$this->{property-name}->validate());
        if (\$form->hasErrors()) {
            return;
        }

        \$this->{property-name}->save();

        Alert::addSuccess('Form save successfully.');
        \$action->setRedirect(Uri::create()->set('id', \$this->{property-name}->getId()));
        if (\$form->getTriggeredAction()->isExit()) {
            \$action->setRedirect(Uri::create('/{property-name}Manager'));
        }
    }

    public function show(): ?Template
    {
        // Setup field group widths with bootstrap classes
        //\$this->getForm()->getField('username')->addFieldCss('col-6');
        //\$this->getForm()->getField('email')->addFieldCss('col-6');

        \$renderer = \$this->getFormRenderer();
        \$renderer->addFieldCss('mb-3');

        return \$renderer->show();
    }


    public function get{classname}(): ?\{db-namespace}\{classname}
    {
        return \$this->{property-name};
    }

    public function set{classname}(\${property-name})
    {
        return \$this->{property-name} = \${property-name};
    }

}
PHP;
        return \Tk\CurlyTemplate::create($classTpl);
    }


}
