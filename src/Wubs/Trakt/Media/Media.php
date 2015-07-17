<?php
/**
 * Created by PhpStorm.
 * User: bwubs
 * Date: 01/03/15
 * Time: 22:30
 */

namespace Wubs\Trakt\Media;


use League\OAuth2\Client\Token\AccessToken;
use Wubs\Trakt\ClientId;
use Wubs\Trakt\Request\Calendars\MyShows;
use Wubs\Trakt\Request\CheckIn\Create;
use Wubs\Trakt\Request\CheckIn\Delete;
use Wubs\Trakt\Request\Comments\PostComment;
use Wubs\Trakt\Request\Movies\Comments;
use Wubs\Trakt\Request\Parameters\Comment;
use Wubs\Trakt\Request\Parameters\MediaId;
use Wubs\Trakt\Request\Parameters\Parameter;
use Wubs\Trakt\Request\Parameters\Query;
use Wubs\Trakt\Request\Parameters\Type;
use Wubs\Trakt\Request\Parameters\Year;
use Wubs\Trakt\Request\Search\Text;

abstract class Media
{
    protected $json;

    protected $standard = [];

    protected $id;
    /**
     * @var AccessToken
     */
    protected $token;

    protected $type;

    /**
     * @param $json
     * @param $ClientId
     * @param AccessToken $token
     */
    public function __construct($json, $ClientId, AccessToken $token)
    {
        $this->json = $json;
        $this->id = $ClientId;
        $this->token = $token;

        $this->media = $this->getMedia($json);

        $this->setMediaFields();
    }

    public function json()
    {
        $json = $this->getStandardFields();

        return json_encode($json);
    }

    /**
     * @return array
     */
    public function getStandardFields()
    {
        $fields = [];
        foreach ($this->standard as $item) {
            $fields[$item] = $this->media->{$item};
        }
        return $fields;
    }

    public static function search($id, AccessToken $token, Parameter $query, Year $year = null)
    {
        if ($query instanceof Query) {
            return Text::make($id, $token, [$query, Type::movie(), $year]);
        }
    }

    public function checkIn(
        array $sharing = [],
        $message,
        $venueId,
        $venueName,
        $appVersion,
        $appDate
    ) {
        return new Create(
            $this->token,
            $this->id,
            $this,
            $sharing,
            $message,
            $venueId,
            $venueName,
            $appVersion,
            $appDate
        );
    }

    public function checkOut()
    {
        return Delete::make($this->id, $this->token);
    }

    public function comment(Comment $comment)
    {
        return PostComment::make($this->id, $this->token, [$this, $comment]);
    }

    /**
     * @return \Wubs\Trakt\Response\Comment[]
     */
    public function comments()
    {
        $slug = $this->getSlug();
        return Comments::make($this->id, $this->token, [MediaId::set($slug)]);
    }

    public function getTitle()
    {
        return $this->media->title;
    }

    public function getIds()
    {
        return $this->media->ids;
    }

    public function getSlug()
    {
        $ids = $this->getIds();
        return $ids->slug;
    }

    protected function setMediaFields()
    {
        foreach ($this->media as $key => $value) {
            $this->{$key} = $value;
        }
        foreach ($this->json as $key => $value) {
            if ($key != $this->type) {
                $this->{$key} = $value;
            }
        }

    }

    /**
     * @param $json
     */
    private function getMedia($json)
    {
        if (property_exists($json, "type")) {
            if ($this instanceof Movie) {
                $this->type = Type::movie();
                return $json->movie;
            }
            if ($this instanceof Show) {
                $this->type = Type::show();
                return $json->show;
            }

            if ($this instanceof Person) {
                $this->type = Type::person();
                return $json->person;
            }
        }

        return $this->json;
    }
}