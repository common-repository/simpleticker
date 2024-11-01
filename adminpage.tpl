<form action="#" method="post">
<table width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Ticker updates (every x minutes)</th>
            <th>Message fades (after x secondes)</th>
            <th>Number of fading messages</th>
            <th>Show no messages created x minutes ago</th>
            <th>Password</th>
            <th>Delete</th>
            <th>Copy</th>
        </tr>
    </thead>
    <tbody>
{tickerlist}
        <tr>
            <td align='center'>{id}</td>
            <td align='center'><input type="text" name="ticker[{id}][name]" value="{name}" /></td>
            <td align='center'><input type="text" name="ticker[{id}][updateInterval]" value="{updateInterval}" /></td>
            <td align='center'><input type="text" name="ticker[{id}][messageTimeout]" value="{messageTimeout}" /></td>
            <td align='center'><input type="text" name="ticker[{id}][messageCount]" value="{messageCount}" /></td>
            <td align='center'><input type="text" name="ticker[{id}][tickerTimeout]" value="{tickerTimeout}" /></td>
            <td align='center'><input type="text" name="ticker[{id}][passwd]" value="" /></td>
            <td align='center'><input type="checkbox" name="ticker[{id}][delete]" value="1" /></td>
            <td align='center'>
                <a href="javascript: window.alert('RSS Feed URL:\n\n{pluginpath}/SimpleTicker.php?action=rssFeed&name={name} \n\nor\n\n{pluginpath}/SimpleTicker.php?action=rssFeed&name={id}');">RSS</a>
            </td>
        </tr>
{/tickerlist}
    </tbody>
    <tfooter>
        <tr>
            <td></td>
            <td align="center"><input type="text" name="ticker[new][name]"/></td>
            <td align="center"><input type="text" name="ticker[new][updateInterval]"/></td>
            <td align="center"><input type="text" name="ticker[new][messageTimeout]"/></td>
            <td align="center"><input type="text" name="ticker[new][messageCount]"/></td>
            <td align="center"><input type="text" name="ticker[new][tickerTimeout]"/></td>
            <td align="center"><input type="text" name="ticker[new][passwd]"/></td>
            <td colspan="2"></td>
        </tr>
    </tfooter>
</table>
<p align="center"><input type="submit" value="Update" /></p></form>

<form action="#" method="post">
<table width='100%'>
    <thead>
        <tr>
            <th>Ticker</th>
            <th>Message</th>
            <th>Created</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
{messagelist}
            <tr>
                <td align="center">{name}</td>
                <td align="left">{message}</td>
                <td align="center">{createdOn}</td>
                <td align="center"><input type="checkbox" name="msg[{tickerId}][delete]" value="1" /></td>
            </tr>
{/messagelist}
    </tbody>
    <tfooter>
        <tr><td colspan="4">&nbsp;</td></tr>
        <tr>
            <td align="center">
                <select name="msg[new][ticker]">
                    {tickeridlist}
                </select>
            </td>
            <td align="center"><input type="text" name="msg[new][message]" size="100"/></td>
            <td align="left"><input type="submit" value="Create new message / delete selected messages"/></td>
            <td></td>
        </tr>
    </tfooter>
</table>
</form>