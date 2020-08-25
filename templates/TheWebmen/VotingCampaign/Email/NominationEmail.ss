<h2>Your nomination for the campaign: $Nomination.Campaign.Title</h2>
<p>
    First name: $Nomination.FirstName<br />
    Surname: $Nomination.Surname<br />
    Email address: $Nomination.EmailAddress<br />
    <% loop $Nomination.ParsedExtraFieldsData %>
        <% if $FieldClass == 'SilverStripe\Forms\FileField' %>
            $Title: $Value.Title<br />
        <% else %>
            $Title: $Value<br />
        <% end_if %>
    <% end_loop %>
</p>
