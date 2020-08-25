<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * @property string $Title
 * @property DBDatetime $NominationClosingDateTime
 * @property DBDatetime $VotingStartDateTime
 * @property DBDatetime $VotingClosingDateTime
 * @property string $ConfirmEmailSender
 * @property string $ConfirmEmailSubject
 * @property bool $CampaignUsesCodes
 * @property string $NominateFormSuccessText
 * @property bool $EnableNominationEmail
 * @property string $NominationEmailFrom
 * @property string $NominationEmailSubject
 * @property bool $EnableNominationAdminEmail
 * @property string $NominationAdminEmailFrom
 * @property string $NominationAdminEmailTo
 * @property string $NominationAdminEmailSubject
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
        'CampaignUsesCodes' => 'Boolean',
        'EnableVoteConfirmation' => 'Boolean',
        'NominateFormSuccessText' => 'Text',
        'EnableNominationEmail' => 'Boolean',
        'NominationEmailFrom' => 'Varchar',
        'NominationEmailSubject' => 'Varchar',
        'EnableNominationAdminEmail' => 'Boolean',
        'NominationAdminEmailFrom' => 'Varchar',
        'NominationAdminEmailTo' => 'Varchar',
        'NominationAdminEmailSubject' => 'Varchar',
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

        $fields->removeByName([
            'NominationEmailFrom',
            'NominationEmailSubject',
            'NominationAdminEmailFrom',
            'NominationAdminEmailTo',
            'NominationAdminEmailSubject',
        ]);

        $fields->addFieldToTab('Root.Main', TextField::create('NominateFormSuccessText', 'Nominate form success text'));

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

        $fields->addFieldsToTab('Root.Email', [
            CheckboxField::create('EnableNominationAdminEmail', 'Enable nomination admin email'),
            Wrapper::create([
                TextField::create('NominationAdminEmailFrom', 'Nomination admin email from'),
                TextField::create('NominationAdminEmailTo', 'Nomination admin email to'),
                TextField::create('NominationAdminEmailSubject', 'Nomination admin email subject'),
            ])->displayIf('EnableNominationAdminEmail')->isChecked()->end(),
            CheckboxField::create('EnableNominationEmail', 'Enable nomination email'),
            Wrapper::create([
                TextField::create('NominationEmailFrom', 'Nomination email from'),
                TextField::create('NominationEmailSubject', 'Nomination email subject'),
            ])->displayIf('EnableNominationEmail')->isChecked()->end(),
        ]);

        return $fields;
    }
}
