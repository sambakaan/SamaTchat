<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Notifications\MessageReceived;
use App\Repository\ConversationsRepository;
use App\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationsController extends Controller
{
	private $r;
	private $auth;


	function __construct(ConversationsRepository $conversationsRepository, AuthManager $auth)
	{
      $this->middleware('auth');
		$this->r = $conversationsRepository;
		$this->auth = $auth;
	}

   public function index () {

   		return view('conversations/index',[
   			'users' => $this->r->getConversations($this->auth->user()->id),
   			'unread' => $this->r->unReadCount($this->auth->user()->id),
   		]);

   }

   public function show (User $user ) {
   		$me = $this->auth->user();
   		$messages = $this->r->getMessagesFor($me->id, $user->id)->paginate(50);
   		$unread = $this->r->unReadCount($me->id);
   		if (isset($unread[$user->id])) {
   			$this->r->readAllFrom($user->id,$me->id);
   			unset($unread[$user->id]);
   		}
	   	return view('conversations/show',[
	   		'users' => $this->r->getConversations($me->id),
	   		'unread' => $unread,
	   		'user' => $user,
	   		'messages' => $messages
	   	]);

   }

   public function store (User $user, StoreMessageRequest $request) {

   		$message = $this->r->createMessage(
   			$request->get('content'),
   			$this->auth->user()->id,
   			$user->id
   		);

         $user->notify(new MessageReceived($message));
   		return redirect(route('conversations.show',['id' => $user->id]));

   }
}
