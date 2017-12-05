use Goutte\Client;

$client = new Client();

$crawler = $client->request('GET', 'https://www.symfony.com/blog/');

print($crawler)