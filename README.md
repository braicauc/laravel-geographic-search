# laravel-geographic-search
Return results from the given coordinates latitude and longitude

How to use this model?
For example: if you have latitude an longitude of the users locations you can search in your database places or other things for your user. If you want to show changes or posts around users location use it like that:

$posts = new GeographicSearch($lat,$lng);
$posts->table = 'posts';
$posts->geoSearch();

... all the posts from your database around the user location - 50 Km (default).
