<?php

namespace Sunnysideup\UUDI\Extensions;

use Sunnysideup\UUDI\Api\HashCreator;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;

class UUIDExtension extends DataExtension
{
    private static $db = [
        'UUID' => 'Varchar(65)', //32 + 1 + 32
        'PublicUUID' => 'Varchar(12)', //32 + 1 + 32
    ];

    private static $indexes = [
        'UUID' => true,
        'PublicUUID' => true,
    ];

    public function onBeforeWrite()
    {
        $owner = $this->getOwner();
        if (!$owner->UUID) {
            $hash = $this->getHashID();
            if ($hash) {
                $owner->UUID = $this->getHashID();
                $owner->PublicUUID = $this->calculatePublicUUID();
            }
        }
    }

    public static function create_hash_id(string $class, int $id): string
    {
        return md5(sprintf('%s:%s', $class, $id));
    }

    public function calculatePublicUUID(): string
    {
        $owner = $this->getOwner();
        if (!$owner->UUID) {
            $this->onBeforeWrite();
        }
        if (!$owner->UUID) {
            return '';
        }
        $from = strpos($owner->UUID, '_') - 5;

        return str_replace('_', '', substr($owner->UUID, $from, 11));
    }

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        if (!($owner instanceof SiteTree)) {
            $this->updateCMSFieldsForHashId($fields);
        }
    }

    public function updateSettingsFields(FieldList $fields)
    {
        $owner = $this->owner;
        if ($owner instanceof SiteTree) {
            $this->updateCMSFieldsForHashId($fields);
        }
    }

    public function updateCMSFieldsForHashId(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->addFieldsToTab(
            'Root.Security',
            [
                ReadonlyField::create('MyUUID', 'Private UUID', $owner->UUID),
                ReadonlyField::create('MyPublicUUID', 'Public UUID', $owner->PublicUUID),
            ]
        );
    }

    /**
     * Gets a truly unique identifier to the classname and ID.
     */
    protected function getHashID(): ?string
    {
        $owner = $this->getOwner();
        if ($owner->ID) {
            return static::create_hash_id($owner->ClassName, $owner->ID) . HashCreator::generate_hash(32);
        }

        return '';
    }
}
