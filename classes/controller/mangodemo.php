<?php

class Controller_MangoDemo extends Controller_Template {

	public $template = 'mangoDemo.html';

	public function action_index()
	{
		$this->template->content = View::factory('mango/intro.html');
	}

	public function action_demo0()
	{
		$this->template->content = View::factory('mango/columns.html');
	}

	public function action_demo1()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating empty account object
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		$content .= Kohana::debug($account->as_array());

		// now we can use the ID to retrieve it from DB
		$account2 = Mango::factory('account', array(
			'_id' => $account->_id
		))->load();

		// this should be the same account
		$content .= Kohana::debug($account2->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo2()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// simulate $_POST object
		$post = array(
			'email'      => 'user@domain.com',
			'role'       => 'manager',
			'account_id' => $account->_id
		);

		// create empty user object
		$user = Mango::factory('user');

		try
		{
			// validate data
			$post = $user->check($post);

			// create user
			$user
				->values($post)
				->create();

			// show user
			$content .= Kohana::debug($user->as_array());

			// load user by email
			$user2 = Mango::factory('user',array(
				'email' => 'user@domain.com'
			))->load();

			// this should be the same
			$content.= Kohana::debug($user2->as_array());

			// you can access the account from the user object
			$content .= Kohana::debug($user->account->as_array());

			// and you can access the users from the account object
			$users = $account->users;

			$content .= Kohana::debug('account',$account->name,'has',$users->count(),'users');
		}
		catch(Validate_Exception $e)
		{
			$content .= Kohana::debug($e->array->errors());
		}

		// clean up (because account has_many users, the users will be removed too)
		$account->delete();
	}

	public function action_demo3()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		//echo Kohana::debug($account->some_counter); exit;

		$content .= Kohana::debug($account->as_array());

		// atomic update
		$account->name = 'name2';
		$account->some_counter->increment(5);
		$account->update();

		$content .= Kohana::debug($account->as_array());

		// another update
		$account->some_counter->increment();
		$account->update();

		$content .= Kohana::debug($account->as_array());

		$account->delete();
	}

	public function action_demo4()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// create user
		$user = Mango::factory('user',array(
			'role' => 'manager',
			'email' => 'user@domain.com',
			'account_id' => $account->_id
		))->create();

		// create blog
		$blog = Mango::factory('blog',array(
			'title' => 'my first blog',
			'text' => 'hello world',
			'time_written' => time(),
			'user_id' => $user->_id
		))->create();

		// add an embedded has many object
		$comment = Mango::factory('comment',array(
			'name'    => 'John Doe',
			'comment' => 'Hello to you to',
			'time'    => time()
		));

		// to add a comment to blog (atomic) you can choose:
		$blog->add($comment); // OR $blog->comments[] = $comment;

		// save blog 
		$blog->update(); 

		// remove comment
		$blog->remove($comment); // OR unset($blog->comments[0]);

		// save blog
		$blog->update();

		// add comment again
		$blog->comments[] = $comment;

		// add another comment
		$comment2 = Mango::factory('comment',array(
			'name'    => 'Jane Doe',
			'comment' => 'I like your style',
			'time'    => time()
		));

		// add a second comment
		$blog->comments[] = $comment2; // or $blog->add($comment);

		$blog->update();

		// This will show the comments stored IN the blog object
		$content .= Kohana::debug($blog->as_array());

		// You can access the comments
		// $blog->comments->as_array() is also possible
		foreach($blog->comments as $comment)
		{
			$content .= Kohana::debug($comment->as_array());
		}

		// Reload
		$blog2 = Mango::factory('blog', array(
			'_id' => $blog->_id
		))->load();

		$content .= Kohana::debug($blog2->as_array());

		// Remove second comment
		unset($blog2->comments[1]);

		$blog2->update();

		$content .= Kohana::debug($blog2->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo5()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// create user
		$user = Mango::factory('user',array(
			'role' => 'manager',
			'email' => 'user@domain.com',
			'account_id' => $account->_id
		))->create();

		$group1 = Mango::factory('group',array(
			'name' => 'Group1'
		))->create();

		// add HABTM relationship between $user and $group1
		$user->add($group1);

		//SAVE BOTH OBJECTS
		$user->update();
		$group1->update();

		$content .= Kohana::debug($user->as_array(),$group1->as_array());

		// Clean up
		$account->delete();

		// The $group1 object will still exist, although it's related $user is removed
		// lets check if the relationship is gone too:
		$group2 = Mango::factory('group',array(
			'_id' => $group1->_id
		))->load();
		
		$content .= Kohana::debug($group2->as_array());

		$group2->delete();
	}

	public function action_demo6()
	{
		$this->template->bind('content',$content);
		$content = '';

		// creating account
		$account = Mango::factory('account',array(
			'name' => 'testaccount'
		))->create();

		// this is atomic
		$account->categories[] = 'cat1';

		$account->update();

		// as is $account->categories->push('cat1');
		// this isn't (but is possible)
		// $account->categories = array('cat1');
		// $account->update();

		$content .= Kohana::debug($account->as_array());

		// try to push the same value
		$account->categories[] = 'cat1'; 

		echo Kohana::debug($account->as_array());

		$account->categories[] = 'cat2';
		$account->update();

		$content .= Kohana::debug($account->as_array());

		// atomic pull
		$account->categories->pull('cat1');
		// OR
		// unset($account->categories[ $account->categories->find('cat1') ]);
		$account->update();

		$content .= Kohana::debug($account->as_array());

		// Clean up
		$account->delete();
	}

	public function action_demo7()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// An unsaved object
		// All actions should result in a save query without updates/modifiers (it is inserted into DB)
		$content .= '<h1>Unsaved objects</h1>';

		/* Counters */
		$content.='<h2>counters</h2>';
		
		$account = Mango::factory('account');
		$account->some_counter->increment();
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_counter->decrement();
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_counter = 5;
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		/* Sets */
		$content.='<h2>sets</h2>';

		$account = Mango::factory('account');
		$account->categories[] = 'cat1';
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->categories = array('cat1','cat2');
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		/* Arrays */
		$content.='<h2>arrays</h2>';

		$account = Mango::factory('account');
		$account->some_array[] = 'cat1';
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_array['key'] = 'cat1';
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		$account = Mango::factory('account');
		$account->some_array = array('cat1','key'=>'bla');
		$content.=Kohana::debug($account->as_array(),$account->changed(FALSE)) . '<hr>';

		// Saved objects
		// Here we want $modifiers
		$content .= '<h1>Unsaved objects</h1>';

		/* Counters */
		$content.='<h2>counters</h2>';
		
		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_counter->increment(); // this is atomic (uses $inc)
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_counter = 5; // this is NOT atomic (uses $set, not $inc)
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		/* Sets */
		$content.='<h2>sets</h2>';

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->categories[] = 'cat1'; // this is atomic (uses $push)
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->categories = array('cat1','cat2'); // this is not atomic - a full reset of the categories array
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		/* Arrays */
		$content.='<h2>arrays</h2>';

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array[] = 'bla';
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array['key'] = 'bla';
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();

		$account = Mango::factory('account');
		$account->name = 'hello';
		$account->create();
		$account->some_array = array('key' => 'bla', 'blo');
		$content.=Kohana::debug($account->as_array(),$account->changed(TRUE)) . '<hr>';
		$account->delete();
	}

	public function action_demo8()
	{
		$this->template->bind('content',$content);
		$content = '';
		
		// let's simulate a load from DB
		$account = Mango::factory('account')->values(array(
			'_id'=>1,
			'name'=>'test',
			'report' => array(
				'total' => 5,
				'blog1' => array(
					'views' => 4,
					'comments'=> 3
				)
			)
		));
		
		// atomic counters are easy:
		
		$account->report['total']->increment();
		$account->report['blog1']['views']->increment();

		$content .= Kohana::debug($account->changed(TRUE));
		
		// simulate changes were saved
		$account->saved();
		
		// we can even add counters
		$account->report['blog2'] = array('views'=>0,'comments'=>0);

		// and they are ready to use as counter
		$account->report['blog2']['views']->increment();

		// also update an existing counter
		$account->report['blog1']['views']->increment();

		$content .= Kohana::debug($account->changed(TRUE));

		// simulate save
		$account->saved();

		// do some more counting
		$account->report['blog2']['views']->increment();

		// and now it will inc
		$content .= Kohana::debug($account->changed(TRUE));
	}

	public function action_demo9()
	{
		// Note: extension support is useful if you have different classes, that inherit
		// from the same (base) class, but each have different additional columns.

		$this->template->bind('content',$content);
		$content = '';

		// Create a Spyker car object
		// We should have access to the Car_Model columns as well as the Spyker_Model columns
		$car = Mango::factory('spyker',array(
			'price' => 1000,
			'spyker_data' => 'hello'
		));

		// create
		$car->create();

		$content .= Kohana::debug($car->as_array());

		// Now create another car
		$car = Mango::factory('ferrari');
		$car->price = 750;
		$car->ferrari_data = 'world';
		$car->create();

		$content .= Kohana::debug($car->as_array());

		// Now we have 2 cars saved in the cars collection, one ferrari, one spyker
		// Let's check - note we use 'car' in the factory method, but we get a fully
		// extended ferrari/spyker_model in return
		$cars = Mango::factory('car')->load(FALSE);
		foreach($cars as $car)
		{
			$content .= Kohana::debug($car->as_array());

			// clean up
			$car->delete();
		}
	}

	public function action_demo12()
	{
		$this->template->bind('content',$content);

		// add to queue
		for($i = 0; $i < 10; $i++)
		{
			MangoQueue::set('message: ' . $i . ' ' . rand());
		}

		$content = '';

		// remove first from queue
		while($msg = MangoQueue::get())
		{
			$content .= Kohana::debug($msg);
		}

		// add to queue
		for($i = 0; $i < 10; $i++)
		{
			MangoQueue::set('message: ' . $i . ' ' . rand());
		}

		// fetch from queue (don't delete)
		$msgs = array();
		while($msg = MangoQueue::get(0,FALSE))
		{
			$msgs[] = $msg;
			$content .= Kohana::debug($msg);
		}

		// remove from queue
		foreach($msgs as $msg)
		{
			MangoQueue::delete($msg);
		}
	}

	public function action_demo13()
	{
		$this->template->content = 'done';
		
		// add to queue
		for($i = 0; $i < 20; $i++)
		{
			MangoQueue::set('message: ' . $i . ' ' . rand());
		}
	}

	public function action_demo14()
	{
		$this->template->bind('content',$content);

		$content = '';

		// get from queue (of course you should build some sort of CLI daemon, this is just for demo purposes)
		while($key = MangoQueue::get())
		{
			sleep(1);
			$content .= Kohana::debug($key);
		}
	}

}