<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Admin Minecraft page
 */

// Can the user view the AdminCP?
if($user->isLoggedIn()){
	if(!$user->canViewACP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	} else {
		// Check the user has re-authenticated
		if(!$user->isAdmLoggedIn()){
			// They haven't, do so now
			Redirect::to(URL::build('/admin/auth'));
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}

// Check if Minecraft integration is enabled
$minecraft_enabled = $queries->getWhere('settings', array('name', '=', 'mc_integration'));
$minecraft_enabled = $minecraft_enabled[0]->value;

$page = 'admin';
$admin_page = 'minecraft';

?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	
	<?php 
	$title = $language->get('admin', 'admin_cp');
	require('core/templates/admin_header.php'); 
	?>
	
	<link rel="stylesheet" href="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/switchery/switchery.min.css">

  </head>
  <body>
    <?php require('modules/Core/pages/admin/navbar.php'); ?>
	<div class="container">
	  <div class="row">
	    <div class="col-md-3">
		  <?php require('modules/Core/pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
		  <div class="card">
		    <div class="card-block">
			  <h3><?php echo $language->get('admin', 'minecraft'); ?></h3>
              <hr />
              <?php
              if(!isset($_GET['view'])) {
                  // Deal with input
                  if(Input::exists()){
                      // Check token
                      if(Token::check(Input::get('token'))){
                          // Valid token
                          // Process input
                          if(isset($_POST['enable_minecraft'])){
                              // Either enable or disable Minecraft integration
                              $enable_minecraft_id = $queries->getWhere('settings', array('name', '=', 'mc_integration'));
                              $enable_minecraft_id = $enable_minecraft_id[0]->id;

                              $queries->update('settings', $enable_minecraft_id, array(
                                  'value' => Input::get('enable_minecraft')
                              ));

                              // Re-query for Minecraft integration
                              $minecraft_enabled = $queries->getWhere('settings', array('name', '=', 'mc_integration'));
                              $minecraft_enabled = $minecraft_enabled[0]->value;
                          } else {
                              // Integration settings

                          }
                      } else {
                          // Invalid token

                      }
                  }
              ?>
                <form id="enableMinecraft" action="" method="post">
                    <?php echo $language->get('admin', 'enable_minecraft_integration'); ?>
                  <input type="hidden" name="enable_minecraft" value="0">
                  <input name="enable_minecraft" type="checkbox" class="js-switch js-check-change"<?php if ($minecraft_enabled == '1') { ?> checked<?php } ?> value="1"/>
                  <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                </form>

                  <?php
                  if ($minecraft_enabled == '1') {
                  ?>
                    <hr />
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=api'); ?>"><?php echo $language->get('admin', 'minecraft_api'); ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=authme'); ?>"><?php echo $language->get('admin', 'authme_integration'); ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=account_verification'); ?>"><?php echo $language->get('admin', 'account_verification'); ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=servers'); ?>"><?php echo $language->get('admin', 'minecraft_servers'); ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=query_errors'); ?>"><?php echo $language->get('admin', 'query_errors'); ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <a href="<?php echo URL::build('/admin/minecraft/', 'view=banners'); ?>"><?php echo $language->get('admin', 'server_banners'); ?></a>
                          </td>
                        </tr>
                      </table>
                    </div>
                  <?php
                  }
              } else {
                switch($_GET['view']){
                    case 'account_verification':
                      echo '<h4>' . $language->get('admin', 'account_verification') . '</h4>';

                      // Handle input
                      if(Input::exists()){
                        if(Token::check(Input::get('token'))){
                          if(!isset($_POST['premium'])) {
                              if (isset($_POST['use_mcassoc']) && $_POST['use_mcassoc'] == 'on') {
                                  $validate = new Validate();
                                  $validation = $validate->check($_POST, array(
                                      'mcassoc_key' => array(
                                          'required' => true,
                                          'max' => 128
                                      ),
                                      'mcassoc_instance' => array(
                                          'required' => true,
                                          'min' => 32,
                                          'max' => 32
                                      )
                                  ));

                                  if ($validation->passed()) {
                                      // Update settings
                                      $use_mcassoc = $queries->getWhere('settings', array('name', '=', 'verify_accounts'));
                                      $use_mcassoc = $use_mcassoc[0]->id;

                                      $mcassoc_key = $queries->getWhere('settings', array('name', '=', 'mcassoc_key'));
                                      $mcassoc_key = $mcassoc_key[0]->id;

                                      $mcassoc_instance = $queries->getWhere('settings', array('name', '=', 'mcassoc_instance'));
                                      $mcassoc_instance = $mcassoc_instance[0]->id;

                                      $queries->update('settings', $use_mcassoc, array('value' => 1));
                                      $queries->update('settings', $mcassoc_key, array('value' => Input::get('mcassoc_key')));
                                      $queries->update('settings', $mcassoc_instance, array('value' => Input::get('mcassoc_instance')));

                                      $success = $language->get('admin', 'updated_mcassoc_successfully');
                                  } else {
                                      $error = $language->get('admin', 'mcassoc_error');
                                  }
                              }
                          } else {
                            $uuid_linking = $queries->getWhere('settings', array('name', '=', 'uuid_linking'));
                            $uuid_linking = $uuid_linking[0]->id;

                            if(isset($_POST['enable_premium_accounts']) && $_POST['enable_premium_accounts'] == 1)
                              $use_premium = 1;
                            else
                              $use_premium = 0;

                            $queries->update('settings', $uuid_linking, array('value' => $use_premium));
                          }
                        }
                      }

                      // Get UUID linking settings
                      $uuid_linking = $queries->getWhere('settings', array('name', '=', 'uuid_linking'));
                      $uuid_linking = $uuid_linking[0]->value;

                      // Get mcassoc settings
                      $use_mcassoc = $queries->getWhere('settings', array('name', '=', 'verify_accounts'));
                      $use_mcassoc = $use_mcassoc[0]->value;

                      $mcassoc_key = $queries->getWhere('settings', array('name', '=', 'mcassoc_key'));
                      $mcassoc_key = Output::getClean($mcassoc_key[0]->value);

                      $mcassoc_instance = $queries->getWhere('settings', array('name', '=', 'mcassoc_instance'));
                      $mcassoc_instance = Output::getClean($mcassoc_instance[0]->value);
                      ?>
                      <form id="enablePremium" action="" method="post">
                          <?php echo $language->get('admin', 'force_premium_accounts'); ?>
                        <input type="hidden" name="enable_premium_accounts" value="0">
                        <input name="enable_premium_accounts" type="checkbox"
                               class="js-switch js-check-change"<?php if ($uuid_linking == '1') { ?> checked<?php } ?>
                               value="1"/>
                        <input type="hidden" name="premium" value="1">
                        <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                      </form>
                      <?php if($uuid_linking == '1') { ?>
                      <hr/>
                      <div class="alert alert-info">
                          <?php echo $language->get('admin', 'mcassoc_help'); ?>
                      </div>
                        <?php
                        if (isset($error)) echo '<div class="alert alert-danger">' . $error . '</div>';
                        else if (isset($success)) echo '<div class="alert alert-success">' . $success . '</div>';
                        ?>
                      <form action="" method="post">
                        <div class="form-group">
                          <label for="use_mcassoc"><?php echo $language->get('admin', 'verify_with_mcassoc'); ?></label>
                          <input id="use_mcassoc" name="use_mcassoc" type="checkbox" class="js-switch"
                                 <?php if ($use_mcassoc == '1'){ ?>checked <?php } ?>/>
                          </span>
                        </div>
                        <div class="form-group">
                          <label for="mcassoc_key"><?php echo $language->get('admin', 'mcassoc_key'); ?></label>
                          <input type="text" class="form-control" name="mcassoc_key" id="mcassoc_key"
                                 value="<?php echo $mcassoc_key; ?>"
                                 placeholder="<?php echo $language->get('admin', 'mcassoc_key'); ?>">
                        </div>
                        <div class="form-group">
                          <label for="mcassoc_instance"><?php echo $language->get('admin', 'mcassoc_instance'); ?></label>
                          <input type="text" class="form-control" name="mcassoc_instance" id="mcassoc_instance"
                                 value="<?php echo $mcassoc_instance; ?>"
                                 placeholder="<?php echo $language->get('admin', 'mcassoc_instance'); ?>">
                          <p><?php echo $language->get('admin', 'mcassoc_instance_help'); ?></p>
                        </div>
                        <div class="form-group">
                          <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                          <input type="submit" class="btn btn-primary"
                                 value="<?php echo $language->get('general', 'submit'); ?>">
                        </div>
                      </form>
                      <?php
                      }
                      break;

                    case 'authme':
                        echo '<h4>' . $language->get('admin', 'authme_integration') . '</h4>';

                        // Handle input
                        if(Input::exists()){
                            if(Token::check(Input::get('token'))){
                                if(isset($_POST['enable_authme'])){
                                    // Either enable or disable Authme integration
                                    $enable_authme_id = $queries->getWhere('settings', array('name', '=', 'authme'));
                                    $enable_authme_id = $enable_authme_id[0]->id;

                                    $queries->update('settings', $enable_authme_id, array(
                                        'value' => Input::get('enable_authme')
                                    ));
                                } else {
                                    // AuthMe config settings
                                    $validate = new Validate();
                                    $validation = $validate->check($_POST, array(
                                        'hashing_algorithm' => array(
                                            'required' => true
                                        ),
                                        'db_address' => array(
                                            'required' => true
                                        ),
                                        'db_name' => array(
                                            'required' => true
                                        ),
                                        'db_username' => array(
                                            'required' => true
                                        ),
                                        'db_table' => array(
                                            'required' => true
                                        )
                                    ));

                                    if($validation->passed()){
                                        $authme_db = $queries->getWhere('settings', array('name', '=', 'authme_db'));
                                        $authme_db_id = $authme_db[0]->id;
                                        $authme_db = json_decode($authme_db[0]->value);

                                        if(isset($_POST['db_password'])){
                                            $password = $_POST['db_password'];
                                        } else {
                                            if(isset($authme_db->password) && !empty($authme_db->password))
                                                $password = $authme_db->password;
                                            else
                                                $password = '';
                                        }

                                        $result = array(
                                            'address' => Output::getClean(Input::get('db_address')),
                                            'port' => (isset($_POST['db_port']) && !empty($_POST['db_port']) && is_numeric($_POST['db_port'])) ? $_POST['db_port'] : 3306,
                                            'db' => Output::getClean(Input::get('db_name')),
                                            'user' => Output::getClean(Input::get('db_username')),
                                            'pass' => $password,
                                            'table' => Output::getClean(Input::get('db_table')),
                                            'hash' => Output::getClean(Input::get('hashing_algorithm')),
                                            'sync' => Input::get('authme_sync')
                                        );

                                        $cache->setCache('authme_cache');
                                        $cache->store('authme', $result);

                                        $queries->update('settings', $authme_db_id, array(
                                            'value' => json_encode($result)
                                        ));

                                    } else {
                                        $error = $language->get('admin', 'enter_authme_db_details');
                                    }
                                }
                            } else {
                                // Invalid token
                                $error = $language->get('general', 'invalid_token');
                            }
                        }

                        $token = Token::get();

                        // Is Authme enabled?
                        $authme_enabled = $queries->getWhere('settings', array('name', '=', 'authme'));
                        $authme_enabled = $authme_enabled[0]->value;

                        if(isset($error)) echo '<div class="alert alert-danger">' . $error . '</div>';

                        echo '<div class="alert alert-info">' . $language->get('admin', 'authme_integration_info') . '</div>';
                        ?>
                        <form id="enableAuthMe" action="" method="post">
                            <?php echo $language->get('admin', 'enable_authme'); ?>
                            <input type="hidden" name="enable_authme" value="0">
                            <input name="enable_authme" type="checkbox"
                                   class="js-switch js-check-change"<?php if ($authme_enabled == '1') { ?> checked<?php } ?>
                                   value="1"/>
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                        </form>
                        <?php
                        if($authme_enabled == '1'){
                            // Retrieve Authme database details
                            $authme_db = $queries->getWhere('settings', array('name', '=', 'authme_db'));
                            $authme_db = json_decode($authme_db[0]->value);
                            ?>
                        <hr />
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="inputHashingAlgorithm"><?php echo $language->get('admin', 'authme_hash_algorithm'); ?></label>
                                <select id="inputHashingAlgorithm" class="form-control" name="hashing_algorithm">
                                    <option value="bcrypt"<?php echo ((isset($authme_db->hash) && $authme_db->hash == 'bcrypt') ? ' selected' : ''); ?>>bcrypt</option>
                                    <option value="sha1"<?php echo ((isset($authme_db->hash) && $authme_db->hash == 'sha1') ? ' selected' : ''); ?>>SHA1</option>
                                    <option value="sha256"<?php echo ((isset($authme_db->hash) && $authme_db->hash == 'sha256') ? ' selected' : ''); ?>>SHA256</option>
                                    <option value="pbkdf2"<?php echo ((isset($authme_db->hash) && $authme_db->hash == 'pbkdf2') ? ' selected' : ''); ?>>PBKDF2</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="inputDBAddress"><?php echo $language->get('admin', 'authme_db_address'); ?></label>
                                <input type="text" class="form-control" name="db_address" value="<?php echo ((isset($authme_db->address)) ? Output::getClean($authme_db->address) : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inputDBPort"><?php echo $language->get('admin', 'authme_db_port'); ?></label>
                                <input type="text" class="form-control" name="db_port" value="<?php echo ((isset($authme_db->port)) ? Output::getClean($authme_db->port) : '3306'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inputDBName"><?php echo $language->get('admin', 'authme_db_name'); ?></label>
                                <input type="text" class="form-control" name="db_name" value="<?php echo ((isset($authme_db->db)) ? Output::getClean($authme_db->db) : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inputDBUsername"><?php echo $language->get('admin', 'authme_db_user'); ?></label>
                                <input type="text" class="form-control" name="db_username" value="<?php echo ((isset($authme_db->user)) ? Output::getClean($authme_db->user) : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inputDBPassword"><?php echo $language->get('admin', 'authme_db_password'); ?></label>
                                <input type="password" class="form-control" name="db_password">
                            </div>
                            <div class="form-group">
                                <label for="inputDBTable"><?php echo $language->get('admin', 'authme_db_table'); ?></label>
                                <input type="text" class="form-control" name="db_table" value="<?php echo ((isset($authme_db->table)) ? Output::getClean($authme_db->table) : 'authme'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inputAuthmeSync"><?php echo $language->get('admin', 'authme_password_sync'); ?></label> <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="<?php echo $language->get('general', 'info'); ?>" data-content="<?php echo $language->get('admin', 'authme_password_sync_help'); ?>"></i></span>
                                <input type="hidden" name="authme_sync" value="0">
                                <input id=inputAuthmeSync" name="authme_sync" type="checkbox"
                                       class="js-switch"<?php if (isset($authme_db->sync) && $authme_db->sync == '1') { ?> checked<?php } ?>
                                       value="1"/>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="token" value="<?php echo $token; ?>">
                                <input type="submit" value="<?php echo $language->get('general', 'submit'); ?>" class="btn btn-primary">
                            </div>
                        </form>
                            <?php
                        }
                        break;

                    case 'servers':
                      if(isset($_GET['action'])){
                        switch($_GET['action']){
                            case 'new':
                              // Handle input
                              if(Input::exists()){
                                  if(Token::check(Input::get('token'))){
                                      // Validate input
                                      $validate = new Validate();
                                      $validation = $validate->check($_POST, array(
                                         'server_name' => array(
                                             'required' => true,
                                             'min' => 1,
                                             'max' => 20
                                         ),
                                         'server_address' => array(
                                             'required' => true,
                                             'min' => 1,
                                             'max' => 64
                                         ),
                                         'server_port' => array(
                                             'max' => 5
                                         ),
                                         'parent_server' => array(
                                             'required' => true
                                         ),
                                         'query_port' => array(
                                             'max' => 5
                                         )
                                      ));

                                      if($validation->passed()){
                                          // Handle input
                                          try {
                                              // BungeeCord selected?
                                              if(isset($_POST['bungee_instance']) && $_POST['bungee_instance'] == 1)
                                                  $bungee = 1;
                                              else
                                                  $bungee = 0;

                                              // Pre 1.7?
                                              if(isset($_POST['pre_17']) && $_POST['pre_17'] == 1)
                                                  $pre = 1;
                                              else
                                                  $pre = 0;
                                              // Status enabled?
                                              if(isset($_POST['status_query_enabled']) && $_POST['status_query_enabled'] == 1)
                                                  $status = 1;
                                              else
                                                  $status = 0;

                                              // Player list enabled?
                                              if(isset($_POST['query_enabled']) && $_POST['query_enabled'] == 1)
                                                  $query = 1;
                                              else
                                                  $query = 0;

                                              // Parent server
                                              if($_POST['parent_server'] == 'none')
                                                $parent = 0;
                                              else
                                                $parent = $_POST['parent_server'];

                                              // Validate server port
                                              if(is_numeric(Input::get('server_port')))
                                                $port = Input::get('server_port');
                                              else {
                                                if(!isset($_POST['server_port']) || empty($_POST['server_port']))
                                                  $port = null;
                                                else
                                                  $port = 25565;
                                              }

                                              // Validate server query port
                                              if(is_numeric(Input::get('query_port')))
                                                $query_port = Input::get('query_port');
                                              else
                                                $query_port = 25565;

                                              $queries->create('mc_servers', array(
                                                  'ip' => Output::getClean(Input::get('server_address')),
                                                  'query_ip' => Output::getClean(Input::get('server_address')),
                                                  'name' => Output::getClean(Input::get('server_name')),
                                                  'display' => $status,
                                                  'pre' => $pre,
                                                  'player_list' => $query,
                                                  'parent_server' => $parent,
                                                  'bungee' => $bungee,
                                                  'port' => $port,
                                                  'query_port' => $query_port
                                              ));

                                              Session::flash('admin_mc_servers_success', $language->get('admin', 'server_created'));
                                              Redirect::to(URL::build('/admin/minecraft', 'view=servers'));
                                              die();

                                          } catch(Exception $e){
                                              $errors = array($e->getMessage());
                                          }
                                      } else {
                                          // Validation failed
                                          $errors = array();
                                          foreach($validation->errors() as $item){
                                              if(strpos($item, 'is required') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                        $errors[] = $language->get('admin', 'server_name_required');
                                                        break;
                                                      case (strpos($item, 'server_address') !== false):
                                                        $errors[] = $language->get('admin', 'server_address_required');
                                                        break;
                                                      case (strpos($item, 'server_port') !== false):
                                                        $errors[] = $language->get('admin', 'server_port_required');
                                                        break;
                                                      case (strpos($item, 'parent_server') !== false):
                                                        $errors[] = $language->get('admin', 'server_parent_required');
                                                        break;
                                                  }
                                              } else if(strpos($item, 'minimum') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                        $errors[] = $language->get('admin', 'server_name_minimum');
                                                        break;
                                                      case (strpos($item, 'server_address') !== false):
                                                        $errors[] = $language->get('admin', 'server_address_minimum');
                                                        break;
                                                      case (strpos($item, 'server_port') !== false):
                                                        $errors[] = $language->get('admin', 'server_port_minimum');
                                                        break;
                                                  }
                                              } else if(strpos($item, 'maximum') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                        $errors[] = $language->get('admin', 'server_name_maximum');
                                                        break;
                                                      case (strpos($item, 'server_address') !== false):
                                                        $errors[] = $language->get('admin', 'server_address_maximum');
                                                        break;
                                                      case (strpos($item, 'server_port') !== false):
                                                        $errors[] = $language->get('admin', 'server_port_maximum');
                                                        break;
                                                      case (strpos($item, 'query_port') !== false):
                                                        $errors[] = $language->get('admin', 'query_port_maximum');
                                                        break;
                                                  }
                                              }
                                          }
                                      }

                                  } else
                                      // Invalid token
                                      $error = $language->get('general', 'invalid_token');
                              }

                              echo '<h4 style="display:inline">' . $language->get('admin', 'adding_server') . '</h4>';
                              echo '<span class="pull-right"><a class="btn btn-danger" href="' . URL::build('/admin/minecraft', 'view=servers') . '">' . $language->get('general', 'cancel') . '</a></span><hr />';
                              ?>
                                <form action="" method="post">
                                  <?php
                                  if(isset($errors)){
                                    echo '<div class="alert alert-danger"><ul>';
                                    foreach($errors as $error)
                                     echo '<li>' . $error . '</li>';
                                    echo '</div></ul>';
                                  }
                                  ?>
                                  <h4>Server Information</h4>
                                  <div class="form-group">
                                    <label for="InputName"><?php echo $language->get('admin', 'server_name'); ?></label>
                                    <input name="server_name" placeholder="<?php echo $language->get('admin', 'server_name'); ?>" id="InputName" value="<?php echo Output::getClean(Input::get('server_name')); ?>" class="form-control">
                                  </div>
                                  <div class="form-group">
                                    <label for="InputAddress"><?php echo $language->get('admin', 'server_address'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'server_address_help') . '"></i></span>'; ?>
                                    <input name="server_address" placeholder="<?php echo $language->get('admin', 'server_address'); ?>" id="InputAddress" value="<?php echo Output::getClean(Input::get('server_address')); ?>" class="form-control">
                                  </div>
                                  <div class="form-group">
                                    <label for="inputPort"><?php echo $language->get('admin', 'server_port'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'leave_port_empty_for_srv') . '"></i></span>'; ?>
                                    <input name="server_port" placeholder="<?php echo $language->get('admin', 'server_port'); ?>" id="inputPort" value="25565" class="form-control">
                                  </div>
                                  <div class="form-group">
                                    <label for="InputParentServer"><?php echo $language->get('admin', 'parent_server'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'parent_server_help') . '"></i></span>'; ?>
                                    <select id="InputParentServer" class="form-control" name="parent_server">
                                      <option value="none" selected><?php echo $language->get('admin', 'no_parent_server'); ?></option>
                                        <?php
                                        $available_parent_servers = $queries->getWhere('mc_servers', array('parent_server', '=', 0));
                                        if(count($available_parent_servers))
                                            foreach($available_parent_servers as $server)
                                              echo '<option value="' . $server->id . '">' . Output::getClean($server->name) . '</option>';
                                        ?>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="inputBungeeInstance"><?php echo $language->get('admin', 'bungee_instance'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'bungee_instance_help') . '"></i></span>'; ?>
                                    <input type="hidden" name="bungee_instance" value="0">
                                    <input id=inputBungeeInstance" name="bungee_instance" type="checkbox" class="js-switch" value="1"/>
                                  </div>
                                  <div class="form-group">
                                    <label for="inputPre17"><?php echo $language->get('admin', 'pre_1.7'); ?></label>
                                    <input type="hidden" name="pre_17" value="0">
                                    <input id=inputPre17" name="pre_17" type="checkbox" class="js-switch" value="1"/>
                                  </div>
                                    <?php
                                    // Display query information alert only if external query is selected
                                    $external_query = $queries->getWhere('settings', array('name', '=', 'external_query'));
                                    $external_query = $external_query[0]->value;
                                    ?>
                                  <h4>Query Information</h4>
                                  <div class="form-group">
                                    <div class="form-group">
                                      <label for="inputStatusQueryEnabled"><?php echo $language->get('admin', 'enable_status_query'); ?></label><?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'status_query_help') . '"></i></span>'; ?>
                                      <input type="hidden" name="status_query_enabled" value="0">
                                      <input id=inputStatusQueryEnabled" name="status_query_enabled" type="checkbox" class="js-switch" value="1"/>
                                    </div>
                                      <?php
                                      if($external_query == '1'){
                                      ?>
                                    <div class="alert alert-info">
                                      <?php echo $language->get('admin', 'server_query_information'); ?>
                                    </div>
                                      <?php
                                      }
                                      ?>
                                    <div class="form-group">
                                      <label for="inputQueryEnabled"><?php echo $language->get('admin', 'enable_player_list'); ?></label><?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'player_list_help') . '"></i></span>'; ?>
                                      <input type="hidden" name="query_enabled" value="0">
                                      <input id=inputQueryEnabled" name="query_enabled" type="checkbox" class="js-switch" value="1"/>
                                    </div>
                                    <div class="form-group">
                                      <label for="inputQueryPort"><?php echo $language->get('admin', 'server_query_port'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'server_query_port_help') . '"></i></span>'; ?>
                                      <input name="query_port" placeholder="<?php echo $language->get('admin', 'server_query_port'); ?>" id="inputQueryPort" value="25565" class="form-control">
                                    </div>
                                  </div>
                                  <hr />
                                  <div class="form-group">
                                    <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                                    <input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
                                  </div>
                                </form>
                              <?php
                              break;
                            case 'edit':
                              // Get server
                              if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
                                Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                die();
                              }

                              $server_editing = $queries->getWhere('mc_servers', array('id', '=', $_GET['id']));
                              if(!count($server_editing)){
                                Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                die();
                              }
                              $server_editing = $server_editing[0];

                              // Handle input
                              if(Input::exists()){
                                  if(Token::check(Input::get('token'))){
                                      // Validate input
                                      $validate = new Validate();
                                      $validation = $validate->check($_POST, array(
                                          'server_name' => array(
                                              'required' => true,
                                              'min' => 1,
                                              'max' => 20
                                          ),
                                          'server_address' => array(
                                              'required' => true,
                                              'min' => 1,
                                              'max' => 64
                                          ),
                                          'server_port' => array(
                                              'max' => 5
                                          ),
                                          'parent_server' => array(
                                              'required' => true
                                          ),
                                          'query_port' => array(
                                              'max' => 5
                                          )
                                      ));

                                      if($validation->passed()){
                                          // Handle input
                                          try {
                                              // BungeeCord selected?
                                              if(isset($_POST['bungee_instance']) && $_POST['bungee_instance'] == 1)
                                                  $bungee = 1;
                                              else
                                                  $bungee = 0;

                                              // Pre 1.7?
                                              if(isset($_POST['pre_17']) && $_POST['pre_17'] == 1)
                                                  $pre = 1;
                                              else
                                                  $pre = 0;

                                              // Status enabled?
                                              if(isset($_POST['status_query_enabled']) && $_POST['status_query_enabled'] == 1)
                                                  $status = 1;
                                              else
                                                  $status = 0;

                                              // Player list enabled?
                                              if(isset($_POST['query_enabled']) && $_POST['query_enabled'] == 1)
                                                  $query = 1;
                                              else
                                                  $query = 0;

                                              // Parent server
                                              if($_POST['parent_server'] == 'none')
                                                  $parent = 0;
                                              else
                                                  $parent = $_POST['parent_server'];

                                              // Validate server port
                                              if(is_numeric(Input::get('server_port')))
                                                  $port = Input::get('server_port');
                                              else {
                                                  if(!isset($_POST['server_port']) || empty($_POST['server_port']))
                                                      $port = null;
                                                  else
                                                      $port = 25565;
                                              }

                                              // Validate server query port
                                              if(is_numeric(Input::get('query_port')))
                                                  $query_port = Input::get('query_port');
                                              else
                                                  $query_port = 25565;

                                              $queries->update('mc_servers', $server_editing->id, array(
                                                  'ip' => Output::getClean(Input::get('server_address')),
                                                  'query_ip' => Output::getClean(Input::get('server_address')),
                                                  'name' => Output::getClean(Input::get('server_name')),
                                                  'display' => $status,
                                                  'pre' => $pre,
                                                  'player_list' => $query,
                                                  'parent_server' => $parent,
                                                  'bungee' => $bungee,
                                                  'port' => $port,
                                                  'query_port' => $query_port
                                              ));

                                              Session::flash('admin_mc_servers_success', $language->get('admin', 'server_updated'));
                                              Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                              die();

                                          } catch(Exception $e){
                                              $errors = array($e->getMessage());
                                          }
                                      } else {
                                          // Validation failed
                                          $errors = array();
                                          foreach($validation->errors() as $item){
                                              if(strpos($item, 'is required') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                          $errors[] = $language->get('admin', 'server_name_required');
                                                          break;
                                                      case (strpos($item, 'server_address') !== false):
                                                          $errors[] = $language->get('admin', 'server_address_required');
                                                          break;
                                                      case (strpos($item, 'server_port') !== false):
                                                          $errors[] = $language->get('admin', 'server_port_required');
                                                          break;
                                                      case (strpos($item, 'parent_server') !== false):
                                                          $errors[] = $language->get('admin', 'server_parent_required');
                                                          break;
                                                  }
                                              } else if(strpos($item, 'minimum') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                          $errors[] = $language->get('admin', 'server_name_minimum');
                                                          break;
                                                      case (strpos($item, 'server_address') !== false):
                                                          $errors[] = $language->get('admin', 'server_address_minimum');
                                                          break;
                                                      case (strpos($item, 'server_port') !== false):
                                                          $errors[] = $language->get('admin', 'server_port_minimum');
                                                          break;
                                                  }
                                              } else if(strpos($item, 'maximum') !== false){
                                                  switch($item){
                                                      case (strpos($item, 'server_name') !== false):
                                                          $errors[] = $language->get('admin', 'server_name_maximum');
                                                          break;
                                                      case (strpos($item, 'server_address') !== false):
                                                          $errors[] = $language->get('admin', 'server_address_maximum');
                                                          break;
                                                      case (strpos($item, 'server_port') !== false):
                                                          $errors[] = $language->get('admin', 'server_port_maximum');
                                                          break;
                                                      case (strpos($item, 'query_port') !== false):
                                                          $errors[] = $language->get('admin', 'query_port_maximum');
                                                          break;
                                                  }
                                              }
                                          }
                                      }

                                  } else
                                      // Invalid token
                                      $error = $language->get('general', 'invalid_token');
                              }

                              echo '<h4 style="display:inline">' . $language->get('admin', 'editing_server') . '</h4>';
                              echo '<span class="pull-right"><a class="btn btn-danger" href="' . URL::build('/admin/minecraft/', 'view=servers') . '">' . $language->get('general', 'cancel') . '</a></span><hr />';
                              ?>
                              <form action="" method="post">
                                  <?php
                                  if(isset($errors)){
                                      echo '<div class="alert alert-danger"><ul>';
                                      foreach($errors as $error)
                                          echo '<li>' . $error . '</li>';
                                      echo '</div></ul>';
                                  }
                                  ?>
                                <h4>Server Information</h4>
                                <div class="form-group">
                                  <label for="InputName"><?php echo $language->get('admin', 'server_name'); ?></label>
                                  <input name="server_name" placeholder="<?php echo $language->get('admin', 'server_name'); ?>" id="InputName" value="<?php echo Output::getClean($server_editing->name); ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                  <label for="InputAddress"><?php echo $language->get('admin', 'server_address'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'server_address_help') . '"></i></span>'; ?>
                                  <input name="server_address" placeholder="<?php echo $language->get('admin', 'server_address'); ?>" id="InputAddress" value="<?php echo Output::getClean($server_editing->ip); ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                  <label for="inputPort"><?php echo $language->get('admin', 'server_port'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'leave_port_empty_for_srv') . '"></i></span>'; ?>
                                  <input name="server_port" placeholder="<?php echo $language->get('admin', 'server_port'); ?>" id="inputPort" value="<?php echo Output::getClean($server_editing->port); ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                  <label for="InputParentServer"><?php echo $language->get('admin', 'parent_server'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'parent_server_help') . '"></i></span>'; ?>
                                  <select id="InputParentServer" class="form-control" name="parent_server">
                                    <option value="none" selected><?php echo $language->get('admin', 'no_parent_server'); ?></option>
                                      <?php
                                      $available_parent_servers = $queries->getWhere('mc_servers', array('parent_server', '=', 0));
                                      if(count($available_parent_servers))
                                        foreach($available_parent_servers as $server)
                                          if($server->id != $server_editing->id)
                                            echo '<option value="' . $server->id . '"' . (($server_editing->parent_server == $server->id) ? ' selected' : '') . '>' . Output::getClean($server->name) . '</option>';
                                      ?>
                                  </select>
                                </div>
                                <div class="form-group">
                                  <label for="inputBungeeInstance"><?php echo $language->get('admin', 'bungee_instance'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'bungee_instance_help') . '"></i></span>'; ?>
                                  <input type="hidden" name="bungee_instance" value="0">
                                  <input id=inputBungeeInstance" name="bungee_instance" type="checkbox" class="js-switch" value="1"<?php if($server_editing->bungee == 1) echo ' checked'; ?>/>
                                </div>
                                <div class="form-group">
                                  <label for="inputPre17"><?php echo $language->get('admin', 'pre_1.7'); ?></label>
                                  <input type="hidden" name="pre_17" value="0">
                                  <input id=inputPre17" name="pre_17" type="checkbox" class="js-switch" value="1"<?php if($server_editing->pre == 1) echo ' checked'; ?>/>
                                </div>
                                  <?php
                                  // Display query information alert only if external query is selected
                                  $external_query = $queries->getWhere('settings', array('name', '=', 'external_query'));
                                  $external_query = $external_query[0]->value;
                                  ?>
                                <h4>Query Information</h4>
                                <div class="form-group">
                                  <div class="form-group">
                                    <label for="inputStatusQueryEnabled"><?php echo $language->get('admin', 'enable_status_query'); ?></label><?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'status_query_help') . '"></i></span>'; ?>
                                    <input type="hidden" name="status_query_enabled" value="0">
                                    <input id=inputStatusQueryEnabled" name="status_query_enabled" type="checkbox" class="js-switch" value="1"<?php if($server_editing->display == 1) echo ' checked'; ?>/>
                                  </div>
                                    <?php
                                    if($external_query == '1'){
                                        ?>
                                      <div class="alert alert-info">
                                          <?php echo $language->get('admin', 'server_query_information'); ?>
                                      </div>
                                        <?php
                                    }
                                    ?>
                                  <div class="form-group">
                                    <label for="inputQueryEnabled"><?php echo $language->get('admin', 'enable_player_list'); ?></label><?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'player_list_help') . '"></i></span>'; ?>
                                    <input type="hidden" name="query_enabled" value="0">
                                    <input id=inputQueryEnabled" name="query_enabled" type="checkbox" class="js-switch" value="1"<?php if($server_editing->player_list == 1) echo ' checked'; ?>/>
                                  </div>
                                  <div class="form-group">
                                    <label for="inputQueryPort"><?php echo $language->get('admin', 'server_query_port'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'server_query_port_help') . '"></i></span>'; ?>
                                    <input name="query_port" placeholder="<?php echo $language->get('admin', 'server_query_port'); ?>" id="inputQueryPort" value="<?php echo Output::getClean($server_editing->query_port); ?>" class="form-control">
                                  </div>
                                </div>
                                <hr />
                                <div class="form-group">
                                  <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                                  <input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
                                </div>
                              </form>
                              <?php
                              break;

                            case 'delete':
                              // Get server
                              if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
                                  Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                  die();
                              }

                              try {
                                $queries->delete('mc_servers', array('id', '=', $_GET['id']));
                                Session::flash('admin_mc_servers_success', $language->get('admin', 'server_deleted'));
                                Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                die();
                              } catch(Exception $e){
                                Session::flash('admin_mc_servers_error', '<p>' . $language->get('admin', 'unable_to_delete_server') . '</p><p>' . $e->getMessage() . '</p>');
                                  Redirect::to(URL::build('/admin/minecraft/', 'view=servers'));
                                die();
                              }
                              break;

                            default:
                              Redirect::to(URL::build('/admin/minecraft'));
                              die();
                              break;
                        }
                      } else {
                          if(Input::exists()){
                            if(Token::check(Input::get('token'))){
                              if(isset($_POST['default_server']) && is_numeric($_POST['default_server']))
                                $new_default = $_POST['default_server'];
                              else
                                $new_default = 0;


                              if(isset($_POST['external_query']) && $_POST['external_query'] == 1)
                                $external = 1;
                              else
                                $external = 0;

                              // Update database and cache
                              try {
                                  // Default server
                                  if($new_default > 0) {
                                      $current_default = $queries->getWhere('mc_servers', array('is_default', '=', 1));
                                      if(count($current_default) && $current_default[0]->id != $new_default)
                                        $queries->update('mc_servers', $current_default[0]->id, array(
                                            'is_default' => 0
                                        ));

                                      if(!count($current_default) || count($current_default) && $current_default[0]->id != $new_default)
                                        $queries->update('mc_servers', $new_default, array(
                                            'is_default' => 1
                                        ));
                                  }

                                  // External query
                                  $external_query_id = $queries->getWhere('settings', array('name', '=', 'external_query'));
                                  $external_query_id = $external_query_id[0];

                                  $queries->update('settings', $external_query_id->id, array(
                                      'value' => $external
                                  ));

                                  $cache->setCache('query_cache');

                                  $cache->store('query', array(
                                      'default' => $new_default,
                                      'external' => $external
                                  ));

                              } catch(Exception $e){
                                  // Error
                                  $error = $e->getMessage();
                              }

                            } else
                              $error = $language->get('general', 'invalid_token');
                          }
                          echo '<h4 style="display:inline">' . $language->get('admin', 'minecraft_servers') . '</h4>';
                          echo '<span class="pull-right"><a class="btn btn-primary" href="' . URL::build('/admin/minecraft', 'view=servers&amp;action=new') . '">' . $language->get('admin', 'add_server') . '</a></span><br /><hr />';

                          if(Session::exists('admin_mc_servers_success'))
                            echo '<div class="alert alert-success">' . Session::flash('admin_mc_servers_success') . '</div>';

                          if(Session::exists('admin_mc_servers_error'))
                            echo '<div class="alert alert-danger">' . Sesion::flash('admin_mc_servers_error') . '</div>';

                          $servers = $queries->getWhere('mc_servers', array('id', '<>', 0));

                          if(count($servers)){
                              // Servers exist
                              $counter = 1;

                              foreach($servers as $server){
                                if($server->is_default == 1)
                                  $default = $server->id;
                                ?>
                                <strong><?php echo Output::getClean($server->name); ?></strong>
                                <span class="pull-right">
                                  <a class="btn btn-warning btn-sm" href="<?php echo URL::build('/admin/minecraft/', 'view=servers&amp;action=edit&amp;id=' . $server->id); ?>"><i class="fa fa-pencil-square-o fa-fw" aria-hidden="true"></i></a>
                                  <a class="btn btn-danger btn-sm" href="<?php echo URL::build('/admin/minecraft/', 'view=servers&amp;action=delete&amp;id=' . $server->id); ?>" onclick="return confirm('<?php echo $language->get('admin', 'confirm_delete_server'); ?>')"><i class="fa fa-trash-o fa-fw" aria-hidden="true"></i></a>
                                </span>
                                <?php
                                if($counter < count($servers))
                                  echo '<hr />';

                                $counter++;
                              }
                          } else {
                              // No servers exist
                              echo '<div class="alert alert-warning">' . $language->get('admin', 'no_servers_defined') . '</div>';
                          }

                          // Query options
                          $external_query = $queries->getWhere('settings', array('name', '=', 'external_query'));
                          $external_query = $external_query[0]->value;

                          echo '<hr /><h4>' . $language->get('admin', 'query_settings') . '</h4>';
                          ?>
                          <form action="" method="post">
                            <?php if(isset($error)) { ?>
                              <div class="alert alert-danger">
                                <?php echo $error; ?>
                              </div>
                            <?php } ?>
                            <div class="form-group">
                              <label for="inputDefaultServer"><?php echo $language->get('admin', 'default_server'); ?></label>
                              <select id="inputDefaultServer" class="form-control" name="default_server">
                                <option value="none"<?php if(!isset($default)) echo ' selected'; ?>><?php echo $language->get('admin', 'no_default_server'); ?></option>
                                <?php
                                if(count($servers)){
                                  foreach($servers as $server){
                                    echo '<option value="' . $server->id . '"' . (($server->is_default == 1) ? ' selected' : '') . '>' . Output::getClean($server->name) . '</option>';
                                  }
                                }
                                ?>
                              </select>
                            </div>
                            <div class="form-group">
                              <label for="inputExternalQuery"><?php echo $language->get('admin', 'external_query'); ?></label> <?php echo ' <span class="badge badge-info"><i class="fa fa-question-circle" data-container="body" data-toggle="popover" data-placement="top" title="' . $language->get('general', 'info') . '" data-content="' . $language->get('admin', 'external_query_help') . '"></i></span>'; ?>
                              <input type="hidden" name="external_query" value="0">
                              <input id=inputExternalQuery" name="external_query" type="checkbox" class="js-switch" value="1" <?php if($external_query == '1') echo 'checked'; ?>/>
                            </div>
                            <div class="form-group">
                              <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
                              <input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
                            </div>
                          </form>
                          <?php
                      }

                      break;

                    case 'banners':
                      echo '<h4 style="display:inline;">' . $language->get('admin', 'server_banners') . '</h4>';
                      if(isset($_GET['server'])) {
                        echo '<span class="pull-right"><a href="' . URL::build('/admin/minecraft/', 'view=banners') . '" class="btn btn-info">' . $language->get('general', 'back') . '</a></span>';
                        // Get server
                        $server = $queries->getWhere('mc_servers', array('id', '=', $_GET['server']));
                        if(!count($server)){
                          Redirect::to(URL::build('/admin/minecraft/', 'view=banners'));
                          die();
                        }
                        $server = $server[0];
                        echo '<hr />';
                        echo '<p><code>http' . ((defined('FORCE_SSL') && FORCE_SSL === true) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . URL::build('/banner/' . $server->name . '.png') . '</code></p>';
                        echo '<img src="' . URL::build('/banner/' . $server->name) . '" alt="' . Output::getClean($server->name) . '" />';
                      } else {
                        $servers = $queries->getWhere('mc_servers', array('id', '<>', 0));
                        if(count($servers)){
                          echo '<br /><br />';
                          $counter = 1;

                          foreach($servers as $server){
                              ?>
                            <strong><?php echo Output::getClean($server->name); ?></strong>
                            <span class="pull-right">
                                <a class="btn btn-info btn-sm" href="<?php echo URL::build('/admin/minecraft/', 'view=banners&amp;server=' . $server->id); ?>"><i class="fa fa-search" aria-hidden="true"></i></a>
                              </span>
                              <?php
                              if($counter < count($servers))
                                  echo '<hr />';

                              $counter++;
                          }
                        } else {
                          echo '<br /><br /><div class="alert alert-info">' . $language->get('admin', 'no_servers_defined') . '</div>';
                        }
                      }
                      break;

                    case 'query_errors':
                      echo '<h4 style="display:inline;">' . $language->get('admin', 'query_errors') . '</h4>';
                      if(!isset($_GET['id']) && !isset($_GET['action']))
                        echo '<span class="pull-right"><a href="' . URL::build('/admin/minecraft', 'view=query_errors&amp;action=purge') . '" class="btn btn-warning" onclick="return confirm(\'' . $language->get('admin', 'confirm_purge_errors') . '\');">' . $language->get('admin', 'purge_errors') . '</a></span><br /><br />';
                      else
                        echo '<span class="pull-right"><a href="' . (!isset($_GET['id']) ? URL::build('/admin/minecraft/') : URL::build('/admin/minecraft/', 'view=query_errors')) . '" class="btn btn-warning">' . $language->get('general', 'back') . '</a></span><br /><br />';

                      if(!isset($_GET['id'])){
                          if(isset($_GET['action']) && $_GET['action'] == 'purge'){
                            $queries->delete('query_errors', array('id', '<>', 0));
                            Redirect::to(URL::build('/admin/minecraft/', 'view=query_errors'));
                            die();
                          }
                          $query_errors = $queries->orderWhere('query_errors', 'id <> 0', 'DATE', 'DESC');
                          if(count($query_errors)){
                              // Get page
                              if(isset($_GET['p'])){
                                  if(!is_numeric($_GET['p'])){
                                      Redirect::to(URL::build('/admin/minecraft/', 'view=query_errors'));
                                      die();
                                  } else
                                      $p = $_GET['p'];

                              } else {
                                  $p = 1;
                              }

                              // Pagination
                              $paginator = new Paginator();
                              $results = $paginator->getLimited($query_errors, 10, $p, count($query_errors));
                              $pagination = $paginator->generate(7, URL::build('/admin/minecraft/', 'view=query_errors&'));
                              ?>
                            <div class="table-responsive">
                              <table class="table table-striped">
                                <thead>
                                <tr>
                                  <th><?php echo str_replace(':', '', $language->get('admin', 'server_address')); ?></th>
                                  <th><?php echo str_replace(':', '', $language->get('admin', 'server_port')); ?></th>
                                  <th><?php echo str_replace(':', '', $language->get('general', 'date')); ?></th>
                                  <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                for($n = 0; $n < count($results->data); $n++){
                                    ?>
                                  <tr>
                                    <td><?php echo Output::getClean($results->data[$n]->ip); ?></td>
                                    <td><?php echo Output::getClean($results->data[$n]->port); ?></td>
                                    <td><?php echo date('d M Y, H:i', $results->data[$n]->date); ?></td>
                                    <td>
                                      <a href="<?php echo URL::build('/admin/minecraft/', 'view=query_errors&amp;id=' . $results->data[$n]->id); ?>"
                                         class="btn btn-info btn-sm"><i class="fa fa-search fa-fw"></i></a> <a
                                              href="<?php echo URL::build('/admin/minecraft/', 'view=query_errors&amp;action=delete&amp;id=' . $results->data[$n]->id); ?>"
                                              class="btn btn-warning btn-sm"
                                              onclick="return confirm('<?php echo $language->get('admin', 'confirm_query_error_deletion'); ?>')"><i
                                                class="fa fa-trash fa-fw"></i></a></td>
                                  </tr>
                                <?php } ?>
                                </tbody>
                              </table>
                            </div>
                              <?php
                              echo $pagination;
                          } else
                            echo '<div class="alert alert-info">' . $language->get('admin', 'no_query_errors') . '</div>';
                      } else if(!isset($_GET['action'])){
                        if(!is_numeric($_GET['id'])){
                          Redirect::to(URL::build('/admin/minecraft/', 'view=query_errors'));
                          die();
                        }

                        $query_error = $queries->getWhere('query_errors', array('id', '=', $_GET['id']));
                        if(!count($query_error)){
                            Redirect::to(URL::build('/admin/minecraft/', 'view=query_errors'));
                            die();
                        }
                        $query_error = $query_error[0];
                        
                        echo '<strong>' . $language->get('admin', 'viewing_query_error') . '</strong><hr />';
                        echo $language->get('admin', 'server_address') . ': ' . Output::getClean($query_error->ip) . '<br />';
                        echo $language->get('admin', 'server_port') . ': ' . Output::getClean($query_error->port) . '<br />';
                        echo $language->get('general', 'date') . ': ' . date('d M Y, G:i', $query_error->date) . '<br /><br />';
                        echo '<div class="panel panel-danger"><div class="panel-body"><p>' . Output::getClean($query_error->error) . '</p></div></div>';
                      } else {
                        if($_GET['action'] == 'delete'){
                          $queries->delete('query_errors', array('id', '=', $_GET['id']));
                          Redirect::to(URL::build('/admin/minecraft/', 'view=query_errors'));
                          die();
                        }
                      }
                      break;
					
					
					case 'api':
						echo '<h4 style="display:inline;">' . $language->get('admin', 'minecraft_api') . '</h4>';
						
						// Handle input
						if(isset($_GET['action'])){
							$new_api_key_renew = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
							$api_key_renew = $queries->getWhere('settings', array('name', '=', 'mc_api_key'));
                            $api_key_renew = $api_key_renew[0]->id;

                            $queries->update('settings', $api_key_renew, array('value' => $new_api_key_renew));
							Redirect::to(URL::build('/admin/minecraft/', 'view=api'));
						} else {
							if(Input::exists()){
								if(Token::check(Input::get('token'))){
									if(isset($_POST['enable_api'])){
										// Either enable or disable Authme integration
										$enable_api_id = $queries->getWhere('settings', array('name', '=', 'use_api'));
										$enable_api_id = $enable_api_id[0]->id;
										$queries->update('settings', $enable_api_id, array(
											'value' => Input::get('enable_api')
										));
									}
								} else {
									// Invalid token
									$error = $language->get('general', 'invalid_token');
								}
							}
						}							
						$token = Token::get();

						// Is Authme enabled?
						$api_enabled = $queries->getWhere('settings', array('name', '=', 'use_api'));
						$api_enabled = $api_enabled[0]->value;
					  
					  ?>
                        <form id="enableAPI" action="" method="post">
                            <?php echo $language->get('admin', 'minecraft_api_enable'); ?>
                            <input type="hidden" name="enable_api" value="0">
                            <input name="enable_api" type="checkbox"
                                   class="js-switch js-check-change"<?php if ($api_enabled == '1') { ?> checked<?php } ?>
								   value="1"/>
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                        </form>
                        <?php
                        if($api_enabled == '1'){
                            // Retrieve Authme database details
                            $api_key = $queries->getWhere('settings', array('name', '=', 'mc_api_key'));
                            $api_key = $api_key[0]->value;
                            ?>
                        <hr />
                            <div class="form-group">
                                <label for="buttonGenerateNewApiKey"><?php echo $language->get('admin', 'minecraft_api_renew'); ?></label><span class="pull-right"><a class="btn btn-primary" href="<?php echo URL::build('/admin/minecraft/', 'view=api&amp;action=renew'); ?>" onclick="return confirm('<?php echo $language->get('admin', 'minecraft_api_renew_confirm'); ?>');"><i class="fa fa-refresh"></i> <?php echo $language->get('admin', 'minecraft_api_renew_button'); ?></a></span>
                            </div>
                            <div class="form-group">
                                <label for="inputDBAddress"><?php echo $language->get('admin', 'minecraft_api_show'); ?></label><span class="pull-right" data-toggle="collapse" href="#api_key" aria-expanded="false" aria-controls="collapseExample"><a class="btn btn-primary" href=""><i class="fa fa-eye"></i> <?php echo $language->get('admin', 'minecraft_api_show_button'); ?></a></span>
                                <div class="collapse" id="api_key">
								<input type="text" class="form-control" name="api_key" value="<?php echo ((isset($api_key)) ? Output::getClean($api_key) : ''); ?>" readonly>
								</div>
                            </div>
                            <?php
						}
					  
					  break;
                    default:
                      // Invalid
                      Redirect::to(URL::build('/admin/minecraft'));
                      break;
                }
              }
			  ?>
		    </div>
		  </div>
		</div>
	  </div>

    </div>
	
	<?php 
	require('modules/Core/pages/admin/footer.php');
	require('modules/Core/pages/admin/scripts.php'); 
	?>
	
	<script src="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/switchery/switchery.min.js"></script>
	
	<script>
	var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
	elems.forEach(function(html) {
	  var switchery = new Switchery(html);
	});
	
	/*
	 *  Submit form on clicking enable/disable Minecraft/AuthMe
	 */
	var changeCheckbox = document.querySelector('.js-check-change');

	changeCheckbox.onchange = function() {
	  if($("#enableAuthMe").length > 0)
      $('#enableAuthMe').submit();
	  else if($("#enableAPI").length > 0)
	    $('#enableAPI').submit();
	  else if($("#enablePremium").length > 0)
	    $('#enablePremium').submit();
	  else
	    $('#enableMinecraft').submit();
	};
		

		<?php if(isset($_GET['view']) && $_GET['view'] == 'account_verification'){ ?>
  function generateInstance() {
      var text = "";
      var possible = "abcdef0123456789";
      // thanks SO 1349426
      for(var i = 0; i < 32; i++)
          text += (possible.charAt(Math.floor(Math.random() * possible.length)));

      document.getElementById("mcassoc_instance").setAttribute("value", text);
  }
  <?php } ?>
	</script>

  </body>
</html>
