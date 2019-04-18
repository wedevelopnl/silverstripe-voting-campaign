<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;

/**
 * @property string $Title
 * @property DBDatetime $NominationClosingDateTime
 * @property DBDatetime $VotingStartDateTime
 * @property DBDatetime $VotingClosingDateTime
 * @property string $ConfirmEmailSender
 * @property string $ConfirmEmailSubject
 * @property bool $CampaignUsesCodes
 * @method HasManyList Nominations()
 * @method HasManyList Votes()
 * @method HasManyList VotingCodes()
 */
class VotingCampaign extends DataObject {
    private static $table_name = 'VotingCampaign_VotingCampaign';

    private static $db = [
        'Title' => 'Varchar',
        'NominationClosingDateTime' => 'DBDatetime',
        'VotingStartDateTime' => 'DBDatetime',
        'VotingClosingDateTime' => 'DBDatetime',
        'ResultsVisibleDateTime' => 'DBDatetime',
        'ConfirmEmailSender' => 'Varchar(255)',
        'ConfirmEmailSubject' => 'Varchar(255)',
        'CampaignUsesCodes' => 'Boolean'
    ];

    private static $has_many = [
        'Nominations' => Nomination::class,
        'Votes' => Vote::class,
        'VotingCodes' => VotingCode::class
    ];

    private static $summary_fields = [
        'Title',
        'Nominations.Count' => 'Num. Nominations',
        'Votes.Count' => 'Total votes'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        /** @var GridField $nominationsField */
        $nominationsField = $fields->dataFieldByName('Nominations');
        if ($nominationsField) {
            $nominationsField->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        }

        /** @var GridField $votesField */
        $votesField = $fields->dataFieldByName('Votes');
        if ($votesField) {
            $votesField->setConfig(GridFieldConfig_RecordViewer::create());
        }

        $fields->removeByName('VotingCodes');
        if ($this->exists()) {
            $votingCodesConfig = new GridFieldConfig_RecordEditor();
            $votingCodesField = new GridField('VotingCodes', 'Codes', $this->VotingCodes(), $votingCodesConfig);
            $fields->addFieldToTab('Root.Codes', $votingCodesField);
        }

        return $fields;
    }
}
