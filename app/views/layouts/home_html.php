<?php
	class_exists('UserResource') || require('resources/UserResource.php');
	class_exists('ProfileResource') || require('resources/ProfileResource.php');
?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?php echo $title;?></title>
		<link rel="icon" href="<?php echo App::url_for('images/6dicon.png');?>"/>
		<meta name="description" content="<?php echo $description;?>"/>
		<meta name="keywords" content="{$keywords}"/>
		<meta name="viewport" content="width=980"/>
	</head>
	<body class="<?php echo $this->name;?>">
		<div class="view">
			<header id="banner">
				<h1><a href="<?php echo App::url_for(null);?>" title="Home"><span><?php echo Application::$member->profile != null ? Application::$member->profile->site_name : Application::$member->member_name;?></span></a></h1>
			</header>
			<aside id="author">
				<a href="<?php echo Application::url_with_member(null);?>" title="Go back to my home page">
				<?php if(Application::$member->person->profile != null):?>
					<img src="<?php echo ProfileResource::getPhotoUrl(Application::$member->person);?>" alt="photo of <?php echo Application::$member->name;?>" class="author" />
				<?php endif;?>
				</a>
			</aside>
			<section id="content">
				<div id="user_message"<?php echo (Resource::get_user_message() !== null ? ' style="display:block;"' : null);?>>
					<?php echo Resource::get_user_message();?>
				</div>
				<?php echo $output;?>
			</section>
			<?php if(AuthController::is_authorized()):?>
			<nav id="admin_menu" class="main">
				<a id="photos_link" href="<?php echo Application::url_with_member('photos');?>" title="show all photos">media</a>
				<a href="<?php echo Application::url_with_member(null);?>" id="home_link" title="go to your home page">home</a>
				<a href="<?php echo Application::url_with_member('post');?>" id="new_post_link" title="new post">new post</a>
				<a href="<?php echo Application::url_with_member('posts');?>" id="all_posts_link" title="show all posts">posts</a>
				<a href="<?php echo Application::url_with_member('addressbook');?>" id="addressbook_link" title="show your addressbook">addressbook</a>
				<a href="<?php echo Application::url_with_member('profile');?>" id="profile_link" title="show your profile">profile</a>
				<?php if(AuthController::is_super_admin()):?>
				<a href="<?php echo Application::url_with_member('members');?>" id="members_link" title="See all the members in your network">members</a>
				<a href="<?php echo Application::url_with_member('member');?>" id="member_link" title="Create a new member">add a member</a>
				<?php endif;?>
			 	<a href="<?php echo Application::url_with_member('logout');?>" id="logout_link">logout</a>
			</nav>
			<p>Welcome <?php echo Application::$current_user->name;?></p>
			<?php endif;?>
			<footer id="footer">
				<p>&copy;<?php echo date('Y');?> Powered by <a href="http://get6d.com/" title="6d">6d</a></p>
				<nav>
					<?php if(!AuthController::is_authorized()):?>
					<a href="<?php echo App::url_for('login');?>" title="Login">Login</a>
					<?php endif;?>
					<a href="<?php echo Application::url_with_member('blog');?>" title="Blog">Blog</a>
					<a href="<?php echo Application::url_with_member('profile');?>" title="Profile page">Profile</a>
					<a href="<?php echo App::url_for('members');?>" title="Member directory">Members</a>
				</nav>
			</footer>
		</div>
		<noscript>requires Javascript. Please either turn on Javascript or get a browser that supports Javascript to use 6d.</noscript>
	</body>
	<link rel="stylesheet" type="text/css" href="<?php echo App::url_for_theme('css/default.css');?>" media="screen" />	
	<?php echo $resource_css;?>
	<script type="text/javascript" charset="utf-8" src="<?php echo App::url_for('js/NotificationCenter.js');?>"></script>
	<script type="text/javascript" charset="utf-8" src="<?php echo App::url_for('js/default.js');?>" id="default_script"></script>
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<?php echo $resource_js;?>
</html>
