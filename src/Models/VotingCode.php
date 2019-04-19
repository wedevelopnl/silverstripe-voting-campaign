<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Code
 * @property bool $IsUsed
 * @property int $Weight
 * @method VotingCampaign Campaign()
 * @method Vote Vote()
 */
class VotingCode extends DataObject
{
    private static $table_name = 'VotingCampaign_VotingCode';

    private static $db = [
        'Code' => 'Varchar(255)',
        'Weight' => 'Int',
        'IsUsed' => 'Boolean'
    ];

    private static $has_one = [
        'Campaign' => VotingCampaign::class
    ];

    private static $belongs_to = [
        'Vote' => Vote::class
    ];

    private static $summary_fields = [
        'Code',
        'Weight',
        'IsUsed'
    ];

    private static $defaults = [
        'Weight' => 1
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Code) {
            $this->Code = md5(uniqid());
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('CampaignID');

        $fields->dataFieldByName('IsUsed')->setReadonly(true)->setDisabled(true);
        $codeField = $fields->dataFieldByName('Code');
        if ($this->Code) {
            $codeField->setReadonly(true)->setDisabled(true);
        }else{
            $codeField->setDescription('Leave blank to generate a code');
        }

        $vote = $this->Vote();
        if ($vote && $vote->exists()) {
            $fields->addFieldToTab('Root.Main', new ReadonlyField('VoteID', 'Vote', $vote->ID));
        }

        return $fields;
    }

    public function canEdit($member = null)
    {
        return $this->IsUsed ? false : parent::canEdit($member);
    }
}
