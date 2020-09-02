# supermetrics

## The assignment

This task was done according to **consuming-an-api-assignment-1-.pdf** which I'm not publishing here for privacy reasons.

## Configuration

Please put your `client_id` into `config.php`. Without it, the script won't function. I'm not publishing my `client_id` in the repository for security concerns (storing passwords and keys in any repository, even private one, is not really a good idea).

## Running

After you've put `client_id` into configuration file, simply run `index.php`. It could be run either from CLI or via web-server.

## Encoding

Encoding is needed for the longest post to be calculated properly. During development I noticed that current API returns data in UTF-8 encoding, so I created a constant for the encoding in `Analyzer` class. It's used only once by a *mb_strlen()* function.

## Output structure

Since the exact format of the stats output was not detailed (other than being a JSON), I'm return a rather simple JSON object. It has 4 keys, namely `a`, `b`, `c`, and `d`. Each of the objects has one key describing what exactly it is returning and another key for the data itself.

### Average number of posts per user per month

1. I was not sure what to return here - one average number for all the users, or an array of averages for all users, so I'm returning both.

2. Task says API will provide "*1000 posts over a six month period*", but I didn't like the idea of relying on this magic **6** constant. Here's the quote from the code in `Analyzer.php`:

   ```
   This is tricky. Task says "1000 posts over a six month period". But what if we get 1000 posts within last month only? 
   Hard-coding 6 seems to be too optimistic, so instead we record the earliest post and the latest post from the feed.
   Then we calculate an approximate (and decimal) number of months between two timestamps ($months).
   So then we divide number or posts by $months.
   (during development I got data feed that led to $months being slightly greater than 6.2)
   This way this metrics depends on data only, not on an external promise.
   ```

## Date format

Whenever required, script generates months' and weeks' numbers according to [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601). Examples:

- week: `2020W35`
- month: `2020-09`

## Tests

This implementation contains no tests. The code is split into 2 independent classes: `Analyzer` and `Talker` to comply with a single-responsibility principle and make testing easier: test the analyzer separately (doesn't depend on API), test the talker separately (doesn't need to do anything besides fetching data).
