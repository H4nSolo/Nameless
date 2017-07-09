<div class="jumbotron home-header">
  <div class="container">
	{include file='navbar.tpl'}
	<center>
	  <br /><br /><br />
	  <h1>{$SITE_NAME}</h1>

	  {if isset($MINECRAFT)}
		<hr />
		{if isset($SERVER_QUERY)}
		  {if isset($SERVER_QUERY.status_value) && $SERVER_QUERY.status_value == 1}
			{if isset($SERVER_QUERY.status_full)}
				<p class="lg">{$SERVER_QUERY.status_full}</p>
			{else}
			    <p class="lg">{$SERVER_QUERY.x_players_online}</p>
			{/if}
		  {else}
		    <p class="lg">{$SERVER_QUERY.server_offline}</p>
		  {/if}
		{/if}
		<p class="lg">{$CONNECT_WITH}</p>
	  {/if}
	</center>
	
  </div>
</div>

<div class="home-news">
  <div class="container">
    <br />
	{if isset($HOME_SESSION_FLASH)}
        <div class="alert alert-info">
            {$HOME_SESSION_FLASH}
        </div>
	{/if}
	<div class="row">
	
	  {if isset($NEWS)}
	  <div class="col-md-8">
	    <center><h2>{$LATEST_ANNOUNCEMENTS} <i class="fa fa-bullhorn"></i></h2></center>
		<hr />
		{foreach from=$NEWS item=item}
		<div class="card">
		  <div class="card-header">
			<a href="{$item.url}">{$item.title}</a>
			<span class="pull-right" data-toggle="tooltip" title="{$item.date}">{$item.time_ago}</span>
		  </div>
		  <div class="card-block">
			{$item.content}
			<hr />
			<a href="{$item.author_url}"><img class="rounded-circle" src="{$item.author_avatar}" /></a> <a href="{$item.author_url}" style="{$item.author_style}">{$item.author_name}</a>
		    <span class="pull-right"><a href="{$item.url}" class="btn btn-primary btn-sm">{$READ_FULL_POST} &raquo;</a></span>
		  </div>
		</div>
		{/foreach}
	  </div>
	  <div class="col-md-4">
	  
	  {else}
	  <div class="col-md-4 offset-md-4">
	  {/if}
	  
	    <center><h2>{$SOCIAL} <i class="fa fa-users" aria-hidden="true"></i></h2></center>
	    <hr />
		{if $TWITTER}
	    <div class="card">
		  <div class="card-block">
	        {$TWITTER}
		  </div>
		</div>
		{/if}
	    
	  </div>
	</div>
  </div>
</div>

{include file='footer.tpl'}