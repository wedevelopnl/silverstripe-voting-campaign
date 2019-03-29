<% if $Type == 'no-vote' %>
    <p>Vote not found</p>
<% else_if $Type == 'vote-already-confirmed' %>
    <p>Your vote is already confirmed</p>
<% else_if $Type == 'voting-closed' %>
    <p>Voting is closed</p>
<% else %>
    <p>Your vote is confirmed</p>
<% end_if %>
