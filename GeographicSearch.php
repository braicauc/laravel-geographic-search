<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeographicSearch extends Model
{

    // table from database that will be used for search
    // if you want to search in other tables that contains
    // filds with lat and lng you must define other table before start searching
    // Ex: $posts = new GeographicSearch($lat,$lng);
    //     $posts->table = 'posts';
    //     $posts->geoSearch();
    public $table = 'users';

    // radius in Km
    public $radius = 50.0;

    // latitude as double
    private $lat;

    // longitude as double
    private $lng;

    // additional where on the table that`s defined on $table
    public $whereRaw;

    // maxium results returned from the $table
    public $take = 40;



    public function __construct($lat = null, $lng = null) {
          $this->lat = $lat;
          $this->lng = $lng;
    }


    /**
     * @return - an array with results around latitude and logitude defined on __construct
     *         - null if we do not have lat and lng
     */
    public function geoSearch() {

        if ( empty($this->lat) || empty($this->lng) ) {
             return null;
        }

        $sql = " SELECT * FROM ( SELECT c.*,
                                        p.radius,
                                        p.distance_unit
                                              * DEGREES(ACOS(COS(RADIANS(p.latpoint))
                                              * COS(RADIANS(c.lat))
                                              * COS(RADIANS(p.longpoint - c.lng))
                                              + SIN(RADIANS(p.latpoint))
                                              * SIN(RADIANS(c.lat)))) AS distance 
                               FROM ".$this->table." AS c
                               JOIN ( SELECT  ".$this->lat."  AS latpoint,  ".$this->lng." AS longpoint,
                                              ".$this->radius." AS radius,  111.045 AS distance_unit
                                   ) AS p ON 1=1
                               WHERE ";

        empty($this->whereRaw) ? null : ($sql .= $this->whereRaw);

        $sql .= " c.lat  BETWEEN p.latpoint  - (p.radius / p.distance_unit)
                         AND p.latpoint  + (p.radius / p.distance_unit)
                  AND c.lng BETWEEN p.longpoint - (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
                            AND p.longpoint + (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
             ) AS d
             WHERE distance <= radius
             ORDER BY distance ASC";
             
        return collect(\DB::select($sql))->take($this->take)->toArray();

    }


    /**
     * @param Anunt $anunt - make a point from another model of your app that contains lat and lng filed
     * @return the results as an array from geoSearch after we define the coordinates
     */
    public function aroundThisPoint(Object $object) {
        $this->lat = $anunt->lat;
        $this->lng = $anunt->lng;
        return $this->geoSearch();
    }




}
