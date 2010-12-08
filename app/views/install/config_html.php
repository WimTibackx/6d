<form action="<?php echo App::url_for('configuration');?>" method="post">
	<fieldset>
		<legend>Datbase settings</legend>
		<p>
			<label for="should_overwrite_htaccess">Overwrite htaccess file?</label>
			<input name="should_overwrite_htaccess" type="radio" value="true" />Yes
			<input name="should_overwrite_htaccess" type="radio" value="false" />No
		</p>
		<p>
			<label for="user_name">User name</label>
			<input type="text" value="<?php echo $configuration->user_name;?>" name="user_name" />
		</p>
		
		<p>
			<label for="password">Password</label>
			<input type="password" value="<?php echo $configuration->password;?>" name="password" />
		</p>
		
		<p>
			<label for="host">Host</label>
			<input type="text" value="<?php echo $configuration->host;?>" name="host" />
		</p>
		
		<p>
			<label for="database">Database name</label>
			<input type="text" value="<?php echo $configuration->database;?>" name="database" />
		</p>
		
		<p>
			<label for="prefix">Table prefix</label>
			<input type="text" value="<?php echo $configuration->prefix;?>" name="prefix" />
		</p>
	</fieldset>
	<fieldset>
		<legend>Site Settings</legend>
		<p>
			<label for="prefix">Theme</label>
			<input type="text" value="<?php echo $configuration->theme;?>" name="theme" />
		</p>
		<p>
			<label for="email">Admin Email</label>
			<input type="text" value="<?php echo $configuration->email;?>" name="email" />
		</p>
		<p>
			<label for="email">Admin User Name</label>
			<input type="text" value="<?php echo $configuration->site_user_name;?>" name="site_user_name" />
		</p>
		<p>
			<label for="site_password">Site Password</label>
			<input type="password" value="<?php echo $configuration->site_password;?>" name="site_password" />
		</p>

		<p class="button">
			<button type="submit">Save Settings</button>
		</p>
		<input type="hidden" value="put" name="_method" />
		<input type="hidden" value="<?php echo(($configuration->db_type === null || strlen($configuration->db_type) === 0) ? 'MySql' : $configuration->db_type);?>" name="db_type" />		
	</fieldset>
</form>