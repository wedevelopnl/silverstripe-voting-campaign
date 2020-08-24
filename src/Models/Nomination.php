<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Assets\File;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\CompositeField;

/**
 * @property string $FirstName
 * @property string $Surname
 * @property string $EmailAddress
 * @property string $ExtraFieldsData
 * @property string $Status
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
        'FirstName' => 'Varchar',
        'Surname' => 'Varchar',
        'EmailAddress' => 'Varchar(255)',
        'ExtraFieldsData' => 'Text'
    ];

    private static $has_one = [
        'Campaign' => VotingCampaign::class
    ];

    private static $has_many = [
        'Votes' => Vote::class
    ];

    private static $summary_fields = [
        'FirstName',
        'Surname',
        'Status',
        'Campaign.Title' => 'Campaign',
        'Votes.Count' => 'Num votes',
        'VotesWithWeight' => 'Votes with weight'
    ];

    public function getTitle()
    {
        if ($this->FirstName) {
            return $this->FirstName . ' ' . $this->Surname;
        }
        return parent::getTitle();
    }

    public function getVotesWithWeight()
    {
        return Vote::get()->filter([
            'Status' => Vote::STATUS_CONFIRMED,
            'NominationID' => $this->ID
        ])->sum('Weight');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->replaceField('Status', DropdownField::create('Status', null, [
            self::STATUS_NEW => _t(__CLASS__ . '.STATUS_NEW', 'New'),
            self::STATUS_APPROVED => _t(__CLASS__ . '.STATUS_APPROVED', 'Approved'),
            self::STATUS_DISAPPROVED => _t(__CLASS__ . '.STATUS_DISAPPROVED', 'Disapproved')
        ]));

        /** @var GridField $votesField */
        $votesField = $fields->dataFieldByName('Votes');
        if ($votesField) {
            $votesField->setConfig(GridFieldConfig_RecordViewer::create());
        }

        $fields->removeByName('ExtraFieldsData');
        if ($this->ExtraFieldsData) {
            $extraFieldsData = json_decode($this->ExtraFieldsData, true);
            foreach($extraFieldsData as $fieldName => $fieldData) {
                $fieldType = $fieldData['fieldClass'];
                if ($fieldType === FileField::class){
                    $fieldData['value'] = File::get()->byId($fieldData['value']);
                    $fieldType = $fieldData['fieldClass'] = UploadField::class;
                }
                $field = new $fieldType($fieldName, null);
                if ($fieldType === OptionsetField::class || $fieldType === CheckboxSetField::class) {
                    $field->setSource(is_array($fieldData['value']) ? $fieldData['value'] : [$fieldData['value'] => $fieldData['value']]);
                }
                $field->setValue($fieldData['value']);
                $field->setReadOnly(true);
                $field->setDisabled(true);
                if ($fieldType === UploadField::class) {
                    $field = new CompositeField([
                        $field,
                        new LiteralField($fieldName . '-link', "<a href='{$fieldData['value']->CMSEditLink()}' target='_blank'>Open file</a>")
                    ]);
                }
                $fields->addFieldToTab('Root.ExtraFields', $field);
            }
        }

        return $fields;
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $extraFields = $this->ParsedExtraFieldsData();
        if ($this->ExtraFieldsData) {
            $extraFieldsData = json_decode($this->ExtraFieldsData, true);
            foreach($extraFieldsData as $fieldName => $fieldData) {
                $fieldType = $fieldData['fieldClass'];
                if ($fieldType === FileField::class){
                    $file = File::get()->byId($fieldData['value']);
                    if ($file){
                        $file->delete();
                    }
                }
            }
        }
    }

    public function ParsedExtraFieldsData()
    {
        if (!$this->ExtraFieldsData) {
            return null;
        }
        $extraFieldsData = json_decode($this->ExtraFieldsData, true);
        $out = [];
        foreach($extraFieldsData as $fieldName => $fieldData) {
            $fieldType = $fieldData['fieldClass'];
            if ($fieldType === FileField::class){
                $fieldData['value'] = File::get()->byId($fieldData['value']);
            }
            $out[$fieldName] = $fieldData['value'];
        }
        return new ArrayData();
    }

    public function sendEmail($from, $subject)
    {
        $email = Email::create()
            ->setHTMLTemplate('TheWebmen\\VotingCampaign\\Email\\NominationEmail')
            ->setData([
                'Nomination' => $this
            ])
            ->setFrom($from)
            ->setTo($this->Email)
            ->setSubject($subject);
        $email->send();
    }
}
