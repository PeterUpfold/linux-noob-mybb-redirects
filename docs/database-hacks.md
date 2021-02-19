# Additional Database Hacks

Fix up some smileys:

    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<fileStore.core_Emoticons>/emoticons/default_wink.png[/img]', ';)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<fileStore.core_Emoticons>/emoticons/default_smile.png[/img]', ':)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_smile.png[/img]', ':)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>//public/style_emoticons/default/smile.png[/img]', ':)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_sad.png[/img]', ':(')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_wink.png[/img]', ';)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_blink.png[/img]', 'o_O')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_tongue.png[/img]', ':P')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_huh.png[/img]', 'o_O')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_biggrin.png[/img]', ':)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_happy.png[/img]', ':)')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_unsure.png[/img]', ':/')"
    mysql lnmybb -Bse "UPDATE mybb_posts SET message = REPLACE(message, '[img]<___base_url___>/uploads/emoticons/default_cool.png[/img]', 'B)')"
    