<?php

namespace TheWebmen\VotingCampaign\Admins;

use SilverStripe\Admin\ModelAdmin;
use TheWebmen\VotingCampaign\Models\Nomination;
use TheWebmen\VotingCampaign\Models\VotingCampaign;

class VotingCampaignAdmin extends ModelAdmin {
    private static $managed_models = [
        VotingCampaign::class,
        Nomination::class
    ];
    private static $url_segment = 'voting-campaign';
    private static $menu_title = 'Voting campaign';
    private static $menu_icon_class = 'font-icon-circle-star';
}
