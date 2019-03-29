# SilverStripe Voting Campaign

## Create a nomination page
Add the extension to the page class and to the controller:
```yml
YourNominationPage:
  extensions:
    - TheWebmen\VotingCampaign\Extensions\NominationPageExtension
```

```yml
YourNominationPageController:
  extensions:
    - TheWebmen\VotingCampaign\Extensions\NominationPageControllerExtension
```

```yml
YourVotingPage:
  extensions:
    - TheWebmen\VotingCampaign\Extensions\VotingPageExtension
```

```yml
YourVotingPageController:
  extensions:
    - TheWebmen\VotingCampaign\Extensions\VotingPageControllerExtension
```

```yml
YourResultsPage:
  extensions:
    - TheWebmen\VotingCampaign\Extensions\ResultsPageExtension
```
