<?php

namespace Fedirum\Fedirum;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request as GHRequest;
use GuzzleHttp\Exception\RequestException;

class Outbox extends Actor implements RequestHandlerInterface {
    public static function getOutboxLink() {
        return Actor::getBaseLink() . Config::OUTBOX_PATH;
    }
    
    private function getOutboxResponse($username): Response
    {
        $data = array(
            '@context' => array(
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1'
            ),
            'id' => $this->getActorLink($username),
            'type' => 'Person',
            'preferredUsername' => $username,
            'inbox' => Inbox::getInboxLink(),
            'outbox' => OutBox::getOutboxLink(),
            'url' => $this->getActorLink($username)
        );
        return new JsonResponse($data);
    }

    public function handle(Request $request): Response
    {
        $content = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://lemmy.exopla.net.eu.org/create/d/1-change-my-view?created-' . gmdate('Y-m-d\TH:i:s\Z'),
            'type' => 'Create',
                 'to' => [Actor::getActorLink('alie') . '/followers'],
                'cc' => ['https://www.w3.org/ns/activitystreams#Public'],
            'actor' => Actor::getActorLink('alie'),
            'object' => [
                'id' => 'https://lemmy.exopla.net.eu.org/d/1-change-my-view?' . gmdate('Y-m-d\TH:i:s\Z'),
                'url' => 'https://lemmy.exopla.net.eu.org/d/1-change-my-view?' . gmdate('Y-m-d\TH:i:s\Z'),
                'type' => 'Note',
                'published' => gmdate('Y-m-d\TH:i:s\Z'),
                'attributedTo' => Actor::getActorLink('alie'),
                'content' => '<p>Life, what is it but a dream?</p>',
                'to' => [Actor::getActorLink('alie') . '/followers'],
                'cc' => ['https://www.w3.org/ns/activitystreams#Public']
            ]
        ];

        $user = $this->getInfo('alie');
        $send = new Send();        
        if($user) {
            foreach(Followship::where('id', $user->id)->get() as $ship) {
                $response = $send->post('alie', json_encode($content), $ship->inbox);
            }
        }
        return new HtmlResponse('<h1>Access denied.</h1>', 403);
    }
}
