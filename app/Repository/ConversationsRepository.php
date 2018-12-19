<?php 

namespace  App\Repository;

use App\Message;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * 
 */
class ConversationsRepository 
{
	private $user;
	private $message;

	function __construct(User $user, Message $message)
	{
		$this->user = $user;
		$this->message = $message;
	}

	public function getConversations(int $userId)
	{
		$conversations =  $this->user->newQuery()
		           ->select('name', 'id')
		           ->where('id', '!=', $userId)
		           ->get();
		$unread = $this->unReadCount($userId);

		return $conversations;
	}

	public function createMessage(String $content, int $from, int $to)
	{
		return $this->message->newQuery()->create([
			'content' =>$content,
			'from_id' => $from,
			'to_id' => $to,
			'created_at' => Carbon::now()
		]);
	}

	public function getMessagesFor(int $from, int $to): Builder
	{
		return $this->message->newQuery()
					->whereRaw("((from_id = $from AND to_id = $to) OR (from_id = $to AND to_id = $from))")
					->orderBy('created_at', 'DESC')
					->with([
						'from' => function($query) { return $query->select('name', 'id');}
 				]);

	}

	/**
	 * Permet de marquer tous les messages de l'utilisateur comme lu
	 */
	public function readAllFrom(int $from, int $to )
	{
		$this->message->where('from_id', $from)->where('to_id',$to)->update(['read_at' => Carbon::now()]);		
	}

	/**
	 * Récupère le nombre de messages non lus pour chaque conversations
	 */
	public function unReadCount(int $userId)
	{
		return $this->message->newQuery()
		            ->where('to_id', $userId)
		            ->groupBy('from_id')
		            ->selectRaw('from_id, count(id) as count')
		            ->whereRaw('read_at IS NULL')
		            ->get()
		            ->pluck('count','from_id');	
	}
}