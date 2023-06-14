<?php

namespace Sunnysideup\UUDI\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Sunnysideup\UUDI\Api\HashCreator;

/**
 * Class \Sunnysideup\UUDI\Extensions\UUIDExtension.
 *
 * @property Site|SiteAlias|SiteClient|SiteHost|SiteHourlyRate|SiteLink|SiteMember|SiteRelease|SiteTimeRecord|SitesPage|UUIDExtension $owner
 * @property string $UUID
 * @property string $PublicUUID
 */
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

    private $UUIDNeverAgainRaceCondition = false;

    public function onBeforeWrite()
    {
        $owner = $this->getOwner();
        if (! $owner->UUID) {
            $owner->UUID = $this->getHashID();
        }
        if (! $owner->PublicUUID || 'ERROR' === $owner->PublicUUID) {
            $owner->PublicUUID = $this->calculatePublicUUID();
        }
    }

    public function onAfterWrite()
    {
        $owner = $this->getOwner();
        if (! $owner->UUID && false === ! $this->UUIDNeverAgainRaceCondition) {
            $this->UUIDNeverAgainRaceCondition = true;
            $owner->write();
        }
    }

    public static function create_hash_id(string $class, int $id): string
    {
        //todo - is this guessable? and does this matter? Is this a security feature?
        return md5(sprintf('%s:%s', $class, $id));
    }

    public function calculatePublicUUID(): string
    {
        $owner = $this->getOwner();
        if (! $owner->UUID) {
            return '';
        }
        $from = strpos($owner->UUID, '_') - 6;

        return str_replace('_', '', substr($owner->UUID, $from, 13));
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
                    ReadonlyField::create('MyPublicUUID', 'Public UUID', $owner->PublicUUID),
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
            return static::create_hash_id($owner->ClassName, $owner->ID) . '_' . HashCreator::generate_hash(32);
        }

        return '';
    }
}
