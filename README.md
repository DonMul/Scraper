# Scraper
## What does it do?
Well, the name says it all: `Scraper` scrapes the entire internet. It tries to index all possible links between sites
and all pages there are on the internet.

## How do i use it?
<pre>
$settings = [];
$scraper = new \Scraper\Scraper($settings);
$scraper->run();
</pre>

## Settings:
The following settings are possible:
* `database`
    * `engine`: Currently only `MySQL` is allowed
    * `username`: MySQL username
    * `password`: MySQL password
    * `database`: MySQL database
    * `host`: MySQL host
* `requester`
   * `type`: Allowed types: `TOR` and `cURL`
   * `forceNewIdentity`: When using TOR you can force a new identity when you made a request
* `logger`: Allowed values: `NoLogger` and `StdOut`

## Example
### MySQL
<pre>
$settings = [
    'database'  => [
        'engine'    => \Scraper\Database\MySQL::getName(),
        'username'  => 'USERNAME',
        'password'  => 'PASSWORD',
        'database'  => 'DATABASE',
        'host'      => '127.0.0.1'
    ],
    'requester'   => [
        'type'              => \Scraper\Requester\Tor::getName(),
        'forceNewIdentity'  => true,
    ],
    'logger' => \Scraper\Logger\StdOut::getName()
];
</pre>