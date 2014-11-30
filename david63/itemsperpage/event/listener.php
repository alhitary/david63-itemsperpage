<?php
/**
*
* @package Items per Page Extension
* @copyright (c) 2014 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\itemsperpage\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\twig\twig */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config		$config		Config object
	* @param \phpbb\request\request		$request	Request object
	* @param \phpbb\template\twig\twig	$template	Template object
	* @param \phpbb\user                $user		User object
	* @access public
	*/
	public function __construct($config, $request, $template, $user)
	{
		$this->config	= $config;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'					=> 'load_user_data',
			'core.ucp_prefs_view_data'			=> 'add_user_prefs',
			'core.ucp_prefs_view_update_data'	=> 'update_user_prefs',
			'core.acp_board_config_edit_add'	=> 'acp_board_settings',
		);
	}

	/**
	* Set ACP board settings
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function acp_board_settings($event)
	{
		if ($event['mode'] == 'post')
		{
			$new_display_var = array(
				'title'	=> $event['display_vars']['title'],
				'vars'	=> array(),
			);

			foreach ($event['display_vars']['vars'] as $key => $content)
			{
				$new_display_var['vars'][$key] = $content;
				if ($key == 'posts_per_page')
				{
					$new_display_var['vars']['itemsperpage_override'] = array(
						'lang'		=> 'ITEMS_OVERRIDE',
						'validate'	=> 'bool',
						'type'		=> 'radio:yes_no',
						'explain' 	=> true,
					);
				}
			}
			$event->offsetSet('display_vars', $new_display_var);
		}
	}

	/**
	* Add the necessay variables
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_user_prefs($event)
	{
		$data = $event['data'];

		$data = array_merge($data, array(
			'posts_per_page'	=> $this->request->variable('posts_per_page', (!empty($user->data['user_posts_per_page'])) ? $user->data['user_posts_per_page'] : 0),
			'topics_per_page'	=> $this->request->variable('topics_per_page', (!empty($user->data['user_topics_per_page'])) ? $user->data['user_topics_per_page'] : 0),
		));

		$event->offsetSet('data', $data);

		$this->template->assign_vars(array(
			'S_TOPICS_PER_PAGE'	=> $this->user->data['user_topics_per_page'],
			'S_POSTS_PER_PAGE'	=> $this->user->data['user_posts_per_page'],
			'S_ITEMS_OVERRIDE'	=> $this->config['itemsperpage_override'],
		));
	}

	/**
	* Update the sql data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function update_user_prefs($event)
	{
		$sql_ary	= $event['sql_ary'];
		$data		= $event['data'];

		$sql_ary = array_merge($sql_ary, array(
			'user_posts_per_page'	=> $data['posts_per_page'],
			'user_topics_per_page'	=> $data['topics_per_page'],
		));

		$event->offsetSet('sql_ary', $sql_ary);
	}

	/**
	* Load the necessay data during user setup
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function load_user_data($event)
	{
		if ($this->config['itemsperpage_override'] == false)
		{
			// Switch the user vars
			$this->config['posts_per_page']		= ($this->user->data['user_posts_per_page'] > 0) ? $this->user->data['user_posts_per_page'] : $this->config['posts_per_page'];
			$this->config['topics_per_page']	= ($this->user->data['user_topics_per_page'] > 0) ? $this->user->data['user_topics_per_page'] : $this->config['topics_per_page'];

			// Load the language file
			$lang_set_ext	= $event['lang_set_ext'];
			$lang_set_ext[]	= array(
				'ext_name' => 'david63/itemsperpage',
				'lang_set' => 'itemsperpage',
			);
			$event['lang_set_ext'] = $lang_set_ext;
	   }
	}
}