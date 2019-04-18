<?php

namespace TheWebmen\VotingCampaign\Models;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Email\Email;

/**
 * @property string $Email
 * @property string $Status
 * @property string $Hash
 * @method VotingCampaign Campaign()
 * @method Nomination Nomination()
 * @method VotingCode VotingCode()
 */
class Vote extends DataObject
{
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_UNCONFIRMED = 'unconfirmed';

    private static $table_name = 'VotingCampaign_Vote';

    private static $db = [
        'Email' => 'Varchar(255)',
        'Status' => 'Varchar',
        'Hash' => 'Varchar(255)',
        'Weight' => 'Int'
    ];

    private static $has_one = [
        'Campaign' => VotingCampaign::class,
        'Nomination' => Nomination::class,
        'VotingCode' => VotingCode::class
    ];

    private static $summary_fields = [
        'Created',
        'Campaign.Title' => 'Campaign',
        'Nomination.Name' => 'Nomination',
        'Status'
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Hash) {
            $this->Hash = md5(uniqid());
        }
    }

    public function ConfirmUrl()
    {
        return Director::absoluteBaseURL() . 'confirmvote/' . $this->Hash;
    }

    public function sendConfirmEmail($from, $subject)
    {
        $email = Email::create()
            ->setHTMLTemplate('TheWebmen\\VotingCampaign\\Email\\ConfirmVoteEmail')
            ->setData([
                'Vote' => $this
            ])
            ->setFrom($from)
            ->setTo($this->Email)
            ->setSubject($subject);
        $email->send();
    }
}
