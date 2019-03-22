<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Title
 * @method Nomination Nominations()
 */
class VotingCampaign extends DataObject {
    private static $table_name = 'VotingCampaign_VotingCampaign';

    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $has_many = [
        'Nominations' => Nomination::class
    ];

    private static $summary_fields = [
        'Title',
        'Nominations.Count' => 'Num. Nominations'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        /** @var GridField $nominationsField */
        $nominationsField = $fields->dataFieldByName('Nominations');
        if ($nominationsField) {
            $nominationsField->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        }
        return $fields;
    }
}
