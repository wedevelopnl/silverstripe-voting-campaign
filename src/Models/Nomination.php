<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Name
 * @property string $EmailAddress
 * @property string $Text
 * @property string $Status
 * @method Image Photo()
 * @method VotingCampaign Campaign()
 */
class Nomination extends DataObject
{
    const STATUS_NEW = 'new';
    const STATUS_APPROVED = 'approved';
    const STATUS_DISAPPROVED = 'disapproved';

    private static $table_name = 'VotingCampaign_Nomination';

    private static $db = [
        'Status' => 'Varchar',
        'Name' => 'Varchar',
        'EmailAddress' => 'Varchar(255)',
        'Text' => 'Text'
    ];

    private static $has_one = [
        'Photo' => Image::class,
        'Campaign' => VotingCampaign::class
    ];

    private static $owns = [
        'Photo'
    ];

    private static $summary_fields = [
        'Name',
        'Status',
        'Campaign.Title' => 'Campaign'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->replaceField('Status', DropdownField::create('Status', null, [
            self::STATUS_NEW => _t(__CLASS__ . '.STATUS_NEW', 'New'),
            self::STATUS_APPROVED => _t(__CLASS__ . '.STATUS_APPROVED', 'Approved'),
            self::STATUS_DISAPPROVED => _t(__CLASS__ . '.STATUS_DISAPPROVED', 'Disapproved')
        ]));
        /** @var UploadField $potoField */
        $potoField = $fields->dataFieldByName('Photo');
        $potoField->setFolderName('VotingCampaignNominations');
        return $fields;
    }
}
