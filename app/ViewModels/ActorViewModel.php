<?php

namespace App\ViewModels;

use Carbon\Carbon;
use Spatie\ViewModels\ViewModel;

class ActorViewModel extends ViewModel
{
    public $actor;
    public $socials;
    public $credits;

    public function __construct($actor, $socials, $credits)
    {
        $this->actor = $actor;
        $this->socials = $socials;
        $this->credits = $credits;
    }

    public function actor()
    {
        return collect($this->actor)->merge([
            'profile_path' => $this->actor['profile_path'] ? 'https://image.tmdb.org/t/p/w300/'.$this->actor['profile_path'] : 'https://ui-avatars.com/api/?size=300x450&name='.$this->actor['name'],
            'birthday_details' => Carbon::parse($this->actor['birthday'])->format('M d, Y').' ( '.Carbon::parse($this->actor['birthday'])->age.' years old ) in '.$this->actor['place_of_birth']
        ]);
    }

    public function socials()
    {
        return collect($this->socials)->merge([
            'facebook' => $this->socials['facebook_id'] ? 'https://facebook.com/'.$this->socials['facebook_id'] : null,
            'instagram' => $this->socials['instagram_id'] ? 'https://instagram.com/'.$this->socials['instagram_id'] : null,
            'twitter' => $this->socials['twitter_id'] ? 'https://twitter.com/'.$this->socials['twitter_id'] : null,
        ])->only([
            'facebook', 'instagram', 'twitter',
        ]);
    }

    public function knownForDesc()
    {
        $desc = collect($this->credits)->get('cast');

        return collect($desc)->where('media_type', 'movie')->sortByDesc('popularity')->take(5)
                ->map(function($movie){
                    return collect($movie)->merge([
                        'poster_path' => $movie['poster_path'] ? 'https://image.tmdb.org/t/p/w185/'.$movie['poster_path'] : 'https://ui-avatars.com/api/?size=185x278',
                        'title' => isset($movie['title']) ? $movie['title'] : 'Untitled'
                    ]);
                });
    }

    public function credits()
    {
        $credits = collect($this->credits)->get('cast');

        return collect($credits)->map(function($movie) {
            if (isset($movie['release_date'])) {
                $releaseDate = $movie['release_date'];
            } elseif (isset($movie['first_air_date'])) {
                $releaseDate = $movie['first_air_date'];
            } else {
                $releaseDate = '';
            }

            if (isset($movie['title'])) {
                $title = $movie['title'];
            } elseif (isset($movie['name'])) {
                $title = $movie['name'];
            } else {
                $title = 'Untitled';
            }

            return collect($movie)->merge([
                'release_date' => $releaseDate,
                'release_year' => isset($releaseDate) ? Carbon::parse($releaseDate)->format('Y') : 'Future',
                'title' => $title,
                'character' => isset($movie['character']) ? $movie['character'] : '',
            ])->only([
                'release_date', 'release_year', 'title', 'character',
            ]);
        })->sortByDesc('release_date');
    }
}
