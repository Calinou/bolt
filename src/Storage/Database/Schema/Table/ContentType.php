<?php
namespace Bolt\Storage\Database\Schema\Table;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;

class ContentType extends BaseTable
{
    /** @var array Mapping of field type to column type function */
    protected $typeMap =  [
        // Boolean
        'checkbox'       => 'columnBoolean',
        // Date
        'date'           => 'columnDate',
        // Date/time
        'datetime'       => 'columnDateTime',
        // Float
        'float'          => 'columnFloat',
        // Decimal
        'number'         => 'columnDecimal',
        // Integer
        'integer'        => 'columnInteger',
        // String, 256, empty default
        'text'           => 'columnStringNormal',
        'templateselect' => 'columnStringNormal',
        'file'           => 'columnStringNormal',
        // String, 128, not null, empty default
        'slug'           => 'columnStringNotNull',
        // Text, platform default size
        'filelist'       => 'columnText',
        'geolocation'    => 'columnText',
        'html'           => 'columnText',
        'image'          => 'columnText',
        'imagelist'      => 'columnText',
        'markdown'       => 'columnText',
        'select'         => 'columnText',
        'textarea'       => 'columnText',
        'video'          => 'columnText',
    ];

    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        // @codingStandardsIgnoreStart
        $this->table->addColumn('id',             'integer',  ['autoincrement' => true]);
        $this->table->addColumn('slug',           'string',   ['length' => 128]);
        $this->table->addColumn('datecreated',    'datetime', []);
        $this->table->addColumn('datechanged',    'datetime', []);
        $this->table->addColumn('datepublish',    'datetime', ['notnull' => false, 'default' => null]);
        $this->table->addColumn('datedepublish',  'datetime', ['notnull' => false, 'default' => null]);
        $this->table->addColumn('templatefields', 'text',     ['default' => '']);
        $this->table->addColumn('username',       'string',   ['length' => 32, 'default' => '', 'notnull' => false]); // We need to keep this around for backward compatibility. For now.
        $this->table->addColumn('ownerid',        'integer',  ['notnull' => false]);
        $this->table->addColumn('status',         'string',   ['length' => 32]);
        // @codingStandardsIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['slug']);
        $this->table->addIndex(['datecreated']);
        $this->table->addIndex(['datechanged']);
        $this->table->addIndex(['datepublish']);
        $this->table->addIndex(['datedepublish']);
        $this->table->addIndex(['status']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }

    /**
     * Check if the field type is valid.
     *
     * @param string $type
     *
     * @return boolean
     */
    public function isKnownType($type)
    {
        return isset($this->typeMap[$type]);
    }

    /**
     * Add the contenttype's specific fields.
     *
     * @param string  $fieldName
     * @param string  $type
     * @param boolean $addIndex
     */
    public function addCustomFields($fieldName, $type, $addIndex)
    {
        $this->{$this->typeMap[$type]}($fieldName);

        if ($addIndex) {
            $this->table->addIndex([$fieldName]);
        }
    }

    /**
     * Add a column for booleans, default to zero.
     *
     * @param string $fieldName
     */
    private function columnBoolean($fieldName)
    {
        $this->table->addColumn($fieldName, 'boolean', ['default' => 0]);
    }

    /**
     * Add a column for date, not null.
     *
     * @param string $fieldName
     */
    private function columnDate($fieldName)
    {
        $this->table->addColumn($fieldName, 'date', ['notnull' => false]);
    }

    /**
     * Add a column for date/time, not null.
     *
     * @param string $fieldName
     */
    private function columnDateTime($fieldName)
    {
        $this->table->addColumn($fieldName, 'datetime', ['notnull' => false]);
    }

    /**
     * Add a column for decimals.
     *
     * @deprecated
     *
     * @param string $fieldName
     */
    private function columnDecimal($fieldName)
    {
        $this->table->addColumn($fieldName, 'decimal', ['precision' => '18', 'scale' => '9', 'default' => 0]);
    }

    /**
     * Add a column for floats, default to zero.
     *
     * @param string $fieldName
     */
    private function columnFloat($fieldName)
    {
        $this->table->addColumn($fieldName, 'float', ['default' => 0]);
    }

    /**
     * Add a column for integers, default to zero.
     *
     * @param string $fieldName
     */
    private function columnInteger($fieldName)
    {
        $this->table->addColumn($fieldName, 'integer', ['default' => 0]);
    }

    /**
     * Add a column for a 256 character string with an empty string default.
     *
     * @param string $fieldName
     */
    private function columnStringNormal($fieldName)
    {
        $this->table->addColumn($fieldName, 'string', ['length' => 256, 'default' => '']);
    }

    /**
     * Add a column for a 123 character string, not null, with an empty string default.
     *
     * @param string $fieldName
     */
    private function columnStringNotNull($fieldName)
    {
        // Only additional slug fields will be added. If it's the
        // default slug, skip it instead.
        if ($fieldName != 'slug') {
            $this->table->addColumn($fieldName, 'string', ['length' => 128, 'notnull' => false, 'default' => '']);
        }
    }

    /**
     * Add a column text fields.
     *
     * @param string $fieldName
     */
    private function columnText($fieldName)
    {
        $this->table->addColumn($fieldName, 'text', ['default' => $this->getTextDefault()]);
    }

    /**
     * Default value for TEXT fields, differs per platform.
     *
     * @return string|null
     */
    private function getTextDefault()
    {
        if ($this->platform instanceof SqlitePlatform || $this->platform instanceof PostgreSqlPlatform) {
            return '';
        }

        return null;
    }
}
