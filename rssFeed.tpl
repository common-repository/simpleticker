<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
        <title>{name}</title>
        <description>Wordpress Simple Ticker RSS Feed</description>
        <lastBuildDate>{currentTimestamp}</lastBuildDate>
        <pubDate>{lastCreatedOn}</pubDate>
{messages}
        <item>
                <title>{message}</title>
                <guid>{uid}</guid>
                <pubDate>{createdOn}</pubDate>
        </item>
{/messages}
</channel>
</rss>