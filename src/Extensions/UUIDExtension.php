<?php

namespace Sunnysideup\UUDI\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Sunnysideup\UUDI\Api\HashCreator;

/**
 * Class \Sunnysideup\UUDI\Extensions\UUIDExtension.
 *
 * @property string $UUID
 * @property string $PublicUUID
 */
class UUIDExtension extends DataExtension
{
    private static $db = [
        'RequiresUUID' => 'Boolean(0)',
        'UUID' => 'Varchar(65)', //32 + 1 + 32
        'PublicUUID' => 'Varchar(12)',
    ];

    private static $indexes = [
        'RequiresUUID' => true,
        'UUID' => false,
        'PublicUUID' => true,
    ];

    private $UUIDNeverAgainRaceCondition = false;

    public function onBeforeWrite()
    {
        $owner = $this->getOwner();
        if ($this->requiresUUID()) {
            if (! $owner->UUID) {
                $owner->UUID = $this->getHashID();
            }
        } else {
            $this->UUIDNeverAgainRaceCondition = true;
            $owner->UUID = '';
        }
        if (! $owner->PublicUUID || 'ERROR' === $owner->PublicUUID) {
            $owner->PublicUUID = $this->calculatePublicUUID();
        }
    }

    public function onAfterWrite()
    {
        $owner = $this->getOwner();
        if (! $owner->PublicUUID && false === $this->UUIDNeverAgainRaceCondition) {
            $this->UUIDNeverAgainRaceCondition = true;
            $owner->write();
        }
    }

    public function calculatePublicUUID(?int $length = 12): string
    {
        return HashCreator::generate_hash($length);
    }

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        if (! ($owner instanceof SiteTree)) {
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
        /** @var DataObject $owner */
        $owner = $this->owner;
        $fields->removeByName(
            [
                'RequiresUUID',
                'UUID',
                'PublicUUID',
            ]
        );
        if ($owner->hasMethod('ShowUUIDInCMS')) {
            $tab = 'Root.UUID';
            if ($owner->hasMethod('UUIDTabInCMS')) {
                $tab = $owner->UUIDTabInCMS();
            }

            $fields->addFieldsToTab(
                $tab,
                [
                    // ReadonlyField::create('MyUUID', 'Private UUID', $owner->UUID),
                    CheckboxField::create('RequiresUUID', _t('UUIDExtension.RequiresUUID', 'Hash Public UUID'), $owner->PublicUUID)->performDisabledTransformation,
                    ReadonlyField::create('MyPublicUUID', _t('UUIDExtension.PublicUUID', 'Public UUID'), $owner->PublicUUID),
                ]
            );
        }
    }

    /**
     * Gets a truly unique identifier to the classname and ID.
     */
    protected function getHashID(): ?string
    {

        $owner = $this->getOwner();
        if ($owner->ID) {
            return HashCreator::create_hash_id($owner->ClassName, $owner->ID) . '_' . HashCreator::generate_hash(32);
        }

        return '';
    }

    protected function requiresUUID(): bool
    {
        $owner = $this->getOwner();
        return $owner->RequiresUUID ||
            Config::inst()->get(HashCreator::class, 'always_require_uuid') ||
            Config::inst()->get($owner->ClassName, 'always_require_uuid');
    }
}
