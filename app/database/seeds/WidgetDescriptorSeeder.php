<?php

class WidgetDescriptorSeeder extends Seeder
{
    public function run()
    {

        /* WidgetDescriptor: Update or create all */
        /* Webhook / API widgets | CHART */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'webhook_histogram'],
            array(
                'name'         => 'Webhook chart',
                'description'  => 'Building a simple line chart from your data provided by your server in JSON.',
                'type'         => 'webhook_histogram',
                'category'     => 'webhook_api',
                'is_premium'   => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        /* Webhook / API widgets | CHART */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'api_histogram'],
            array(
                'name'         => 'API chart',
                'description'  => 'Building a simple line chart from your data, which you can post to our server any time.',
                'type'         => 'api_histogram',
                'category'     => 'webhook_api',
                'is_premium'   => false,
                'number'       => 2,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        /* Personal widgets */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'clock'],
            array(
                'name'        => 'Clock',
                'description' => 'A simple clock',
                'type'        => 'clock',
                'category'    => 'personal',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 2,
                'min_rows'     => 2,
                'default_cols' => 3,
                'default_rows' => 2
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'quote'],
            array(
                'name'        => 'Quotes',
                'description' => 'Get inspired every day, by this awesome widget.',
                'type'        => 'quote',
                'category'    => 'personal',
                'is_premium'  => false,
                'number'       => 2,
                'min_cols'     => 5,
                'min_rows'     => 1,
                'default_cols' => 12,
                'default_rows' => 2
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'greetings'],
            array(
                'name'        => 'Greetings',
                'description' => 'Wouldn\'t it be great to receive a greeting message from your favourite browser every time you open a new tab?',
                'type'        => 'greetings',
                'category'    => 'personal',
                'is_premium'  => false,
                'number'       => 3,
                'min_cols'     => 5,
                'min_rows'     => 1,
                'default_cols' => 7,
                'default_rows' => 1
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'text'],
            array(
                'name'        => 'Text',
                'description' => 'Insert any text you want in this widget',
                'type'        => 'text',
                'category'    => 'personal',
                'is_premium'  => false,
                'number'       => 4,
                'min_cols'     => 1,
                'min_rows'     => 1,
                'default_cols' => 2,
                'default_rows' => 1
            )
        );

        if (!App::environment('production')) {
            WidgetDescriptor::updateOrCreate(
                ['type' => 'timer'],
                array(
                    'name'         => 'Timer',
                    'description'  => 'A simple timer',
                    'type'         => 'timer',
                    'category'     => 'personal',
                    'is_premium'   => false,
                    'number'       => 5,
                    'min_cols'     => 2,
                    'min_rows'     => 2,
                    'default_cols' => 2,
                    'default_rows' => 2
                )
            );
        } /* !App::environment('production')*/

        if (!App::environment('production')) {

            WidgetDescriptor::updateOrCreate(
                ['type' => 'iframe'],
                array(
                    'name'        => 'Iframe',
                    'description' => 'Include your favourite sites into this dashboard.',
                    'type'        => 'iframe',
                    'category'    => 'personal',
                    'is_premium'  => false,
                    'number'      => 6,
                )
            );

            WidgetDescriptor::updateOrCreate(
                ['type' => 'calculator'],
                array(
                    'name'        => 'Calculator',
                    'description' => 'A simple calculator',
                    'type'        => 'calculator',
                    'category'    => 'personal',
                    'is_premium'  => false,
                    'number'      => 7,
                    'min_cols'     => 2,
                    'min_rows'     => 3,
                    'default_cols' => 2,
                    'default_rows' => 4
                )
            );

            WidgetDescriptor::updateOrCreate(
                ['type' => 'note'],
                array(
                    'name'        => 'Note',
                    'description' => '',
                    'type'        => 'note',
                    'category'    => 'personal',
                    'is_premium'  => false,
                    'number'      => 8,
                )
            );

        } /* !App::environment('production')*/


        /* Financial widgets | STRIPE */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'stripe_mrr'],
            array(
                'name'        => 'Monthly recurring revenue',
                'description' => 'Monthly recurring revenue',
                'type'        => 'stripe_mrr',
                'category'    => 'stripe',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'stripe_arr'],
            array(
                'name'        => 'Annual recurring revenue',
                'description' => 'Annual recurring revenue',
                'type'        => 'stripe_arr',
                'category'    => 'stripe',
                'is_premium'  => false,
                'number'       => 2,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'stripe_arpu'],
            array(
                'name'        => 'Average revenue per user',
                'description' => 'Average revenue per user',
                'type'        => 'stripe_arpu',
                'category'    => 'stripe',
                'is_premium'  => false,
                'number'       => 3,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        if (!App::environment('production')) {
            WidgetDescriptor::updateOrCreate(
                ['type' => 'stripe_events'],
                array(
                    'name'        => 'Events',
                    'description' => 'Your stripe events',
                    'type'        => 'stripe_events',
                    'category'    => 'stripe',
                    'is_premium'  => false,
                    'number'       => 4,
                    'min_cols'     => 3,
                    'min_rows'     => 3,
                    'default_cols' => 3,
                    'default_rows' => 10
                )
            );
        } /* !App::environment('production')*/

        /* Financial widgets | BRAINTREE */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'braintree_mrr'],
            array(
                'name'        => 'Monthly recurring revenue',
                'description' => 'Monthly recurring revenue',
                'type'        => 'braintree_mrr',
                'category'    => 'braintree',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'braintree_arr'],
            array(
                'name'        => 'Annual recurring revenue',
                'description' => 'Annual recurring revenue',
                'type'        => 'braintree_arr',
                'category'    => 'braintree',
                'is_premium'  => false,
                'number'       => 2,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'braintree_arpu'],
            array(
                'name'        => 'Average revenue per user',
                'description' => 'Average revenue per user',
                'type'        => 'braintree_arpu',
                'category'    => 'braintree',
                'is_premium'  => false,
                'number'       => 3,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        /* Social widgets | TWITTER */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'twitter_followers'],
            array(
                'name'        => 'Followers',
                'description' => 'Followers chart',
                'type'        => 'twitter_followers',
                'category'    => 'twitter',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'twitter_mentions'],
            array(
                'name'        => 'Mentions',
                'description' => 'Displays your latest mentions on twitter.',
                'type'        => 'twitter_mentions',
                'category'    => 'twitter',
                'is_premium'  => false,
                'number'       => 3,
                'min_cols'     => 4,
                'min_rows'     => 4,
                'default_cols' => 5,
                'default_rows' => 8
            )
        );

        /* Social widgets | FACEBOOK */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'facebook_likes'],
            array(
                'name'        => 'Likes',
                'description' => 'The total number of people who have liked your Page.',
                'type'        => 'facebook_likes',
                'category'    => 'facebook',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'facebook_engaged_users'],
            array(
                'name'        => 'Engaged users',
                'description' => 'The number of people who engaged with your Page. Engagement includes any click or story created.',
                'type'        => 'facebook_engaged_users',
                'category'    => 'facebook',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'facebook_page_impressions'],
            array(
                'name'         => 'Page impressions',
                'description'  => 'The number of people who have seen any content associated with your Page',
                'type'         => 'facebook_page_impressions',
                'category'     => 'facebook',
                'is_premium'   => false,
                'number'       => 3,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        /* Social widgets | GOOGLE ANALYTICS */
        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_bounce_rate'],
            array(
                'name'        => 'Bounce rate',
                'description' => 'The percentage of single-page session (i.e., session in which the person left your property from the first page).',
                'type'        => 'google_analytics_bounce_rate',
                'category'    => 'google_analytics',
                'is_premium'  => false,
                'number'       => 1,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_sessions'],
            array(
                'name'        => 'Sessions',
                'description' => 'The total number of sessions',
                'type'        => 'google_analytics_sessions',
                'category'    => 'google_analytics',
                'is_premium'  => false,
                'number'       => 2,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_avg_session_duration'],
            array(
                'name'        => 'Average session duration',
                'description' => 'The average duration of user sessions represented in total seconds.',
                'type'        => 'google_analytics_avg_session_duration',
                'category'    => 'google_analytics',
                'is_premium'  => false,
                'number'       => 4,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );


        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_users'],
            array(
                'name'         => 'Users',
                'description'  => 'The total number of unique visitors.',
                'type'         => 'google_analytics_users',
                'category'     => 'google_analytics',
                'is_premium'   => false,
                'number'       => 6,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_active_users'],
            array(
                'name'         => 'Active users',
                'description'  => 'See the number of active users for your page or app.',
                'type'         => 'google_analytics_active_users',
                'category'     => 'google_analytics',
                'is_premium'   => false,
                'number'       => 7,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_goal_completion'],
            array(
                'name'         => 'Goal completion',
                'description'  => 'The number of goal completions for the selected goal.',
                'type'         => 'google_analytics_goal_completion',
                'category'     => 'google_analytics',
                'is_premium'   => false,
                'number'       => 8,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 3,
                'default_rows' => 4
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_top_sources'],
            array(
                'name'        => 'Top sources',
                'description' => 'Display the top sources of your traffic.',
                'type'        => 'google_analytics_top_sources',
                'category'    => 'google_analytics',
                'is_premium'  => false,
                'number'       => 9,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 6,
                'default_rows' => 6
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'google_analytics_conversions'],
            array(
                'name'        => 'Conversions',
                'description' => 'Display the conversion for a goal specified by you.',
                'type'        => 'google_analytics_conversions',
                'category'    => 'google_analytics',
                'is_premium'  => false,
                'number'       => 10,
                'min_cols'     => 3,
                'min_rows'     => 3,
                'default_cols' => 6,
                'default_rows' => 6
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'shared'],
            array(
                'name'         => 'Shared widget',
                'description'  => '',
                'type'         => 'shared',
                'category'     => 'hidden',
                'is_premium'   => false,
            )
        );

        WidgetDescriptor::updateOrCreate(
            ['type' => 'promo'],
            array(
                'name'         => 'Promo widget',
                'description'  => '',
                'type'         => 'promo',
                'category'     => 'hidden',
                'is_premium'   => false,
                'min_cols'     => 1,
                'min_rows'     => 1,
                'default_cols' => 1,
                'default_rows' => 1
            )
        );

        Artisan::call('cache:clear');

        /* Send message to console */
        Log::info('WidgetDescriptorSeeder | All WidgetDescriptors updated, cached.');
    }

} /* WidgetDescriptorSeeder */
