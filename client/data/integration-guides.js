import integrationsCatalog from './forms/integrations.json'

const tierLabels = {
  free: 'Free',
  pro: 'Pro',
  business: 'Business',
  enterprise: 'Enterprise',
}

const guideContent = {
  email: {
    cardDescription: 'Send branded submission emails, internal alerts, PDFs, and reply-to details directly from SharaForms.',
    summary: 'Use SharaForms email notifications to send every submission to the right inbox with custom recipients, rich content, PDF attachments, and optional branding controls.',
    seoDescription: 'Set up SharaForms email notifications with custom recipients, subjects, branded content, reply-to settings, and PDF attachments.',
    steps: [
      'Open your form and go to Integrations.',
      'Choose Email Notification and add one or more recipients.',
      'Set the subject, email content, and optional reply-to details.',
      'Enable submission data or PDF attachments, then save and test.',
    ],
    requirements: [
      'A published or draft form inside SharaForms.',
      'Recipient email addresses. Pro is required for sending beyond your own address.',
      'Optional PDF templates if you want generated files attached.',
    ],
    capabilities: [
      'Multiple recipients with form-field mentions in advanced plans.',
      'Custom sender name, reply-to, and rich email body content.',
      'Optional submission data, hidden fields, edit-submission link, and PDF attachments.',
      'Business-plan email appearance controls for logo, fonts, and colors.',
    ],
    setupSteps: [
      {
        title: 'Add the integration to your form',
        body: 'From the form builder or form settings, open Integrations and select Email Notification.',
      },
      {
        title: 'Choose who should receive notifications',
        body: 'Fill in Send To with one email per line. On higher tiers you can also use form data mentions to route messages dynamically.',
      },
      {
        title: 'Write the email content',
        body: 'Set a sender name, subject, and rich email body. You can include form answers, computed values, and media inside the message.',
      },
      {
        title: 'Turn on optional delivery details',
        body: 'Enable submission data, hidden fields, edit-submission links, reply-to, and PDF template attachments if your workflow needs them.',
      },
      {
        title: 'Test with a real submission',
        body: 'Submit the form once, confirm the message arrives correctly, and adjust the copy or recipients before going live.',
      },
    ],
    tips: [
      'Use your own SMTP setup when you want messages to come from your domain.',
      'Keep subject lines structured so teams can filter notifications quickly.',
      'Attach PDFs only when the recipient really needs a generated document.',
    ],
  },
  slack: {
    cardDescription: 'Push each new form submission into Slack with a webhook and customizable message blocks.',
    summary: 'Send form submission alerts to Slack channels using an incoming webhook URL and message options managed directly in SharaForms.',
    seoDescription: 'Connect SharaForms to Slack with an incoming webhook and customize the notification message for each submission.',
    steps: [
      'Create a Slack incoming webhook for your target channel.',
      'Add the Slack integration inside your form.',
      'Paste the webhook URL and customize the message.',
      'Save and submit a test response to verify delivery.',
    ],
    requirements: [
      'A Slack workspace where you can create incoming webhooks.',
      'A form in SharaForms with permission to edit integrations.',
      'Pro plan access for Slack notifications.',
    ],
    capabilities: [
      'Send a custom notification message for every submission.',
      'Include submission answers, hidden fields, analytics, and quick links.',
      'Route messages into team channels without middleware or code.',
    ],
    setupSteps: [
      {
        title: 'Create the Slack webhook',
        body: 'In Slack, create an incoming webhook for the channel where you want SharaForms notifications to appear.',
      },
      {
        title: 'Paste the webhook URL into SharaForms',
        body: 'Open the Slack integration in your form and paste the webhook URL into the Slack webhook URL field.',
      },
      {
        title: 'Customize the message payload',
        body: 'Edit the notification message and decide whether to include submission data, hidden fields, analytics, or admin links.',
      },
      {
        title: 'Save and run a live submission test',
        body: 'Submit the form and confirm the correct channel receives the expected content.',
      },
    ],
    tips: [
      'Create separate webhooks per channel when different forms notify different teams.',
      'Use field mentions to put the most important answers in the first line.',
    ],
    providerDocsUrl: 'https://api.slack.com/messaging/webhooks',
    providerDocsLabel: 'Slack webhook docs',
  },
  discord: {
    cardDescription: 'Post submission alerts into Discord with webhook-based delivery and configurable message content.',
    summary: 'Connect Discord notifications to your form so every new submission can be pushed into a server channel with the fields and links your team needs.',
    seoDescription: 'Set up the SharaForms Discord integration with a webhook URL and flexible notification message settings.',
    steps: [
      'Create a Discord webhook for the destination channel.',
      'Add the Discord integration in your form settings.',
      'Paste the webhook URL and define the notification content.',
      'Test with a real submission before sharing the form.',
    ],
    requirements: [
      'A Discord server and channel where you can generate webhooks.',
      'A SharaForms form with integration access.',
      'Pro plan access for Discord notifications.',
    ],
    capabilities: [
      'Webhook-based delivery into Discord channels.',
      'Custom message text plus optional submission data and links.',
      'Simple setup without Zapier or custom middleware.',
    ],
    setupSteps: [
      {
        title: 'Generate the Discord webhook',
        body: 'Inside your Discord server settings, create a webhook for the channel that should receive new submission alerts.',
      },
      {
        title: 'Add the webhook URL to the form integration',
        body: 'Paste the generated webhook URL into the Discord webhook URL field in SharaForms.',
      },
      {
        title: 'Decide what the alert should contain',
        body: 'Customize the message text and enable the submission data, hidden fields, and useful links you want the channel to receive.',
      },
      {
        title: 'Validate the delivery',
        body: 'Trigger a test submission and confirm the payload arrives in Discord in the expected format.',
      },
    ],
    tips: [
      'Keep Discord alerts concise and link back to SharaForms for full details.',
      'Use one webhook per channel if different teams need different routing.',
    ],
    providerDocsUrl: 'https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks',
    providerDocsLabel: 'Discord webhook docs',
  },
  telegram: {
    cardDescription: 'Deliver Telegram alerts by connecting an account and reusing SharaForms notification message controls.',
    summary: 'Connect Telegram to SharaForms to receive form submission notifications in chat, using your linked Telegram account and flexible message settings.',
    seoDescription: 'Connect Telegram to SharaForms and send form submission notifications with configurable message content.',
    steps: [
      'Connect a Telegram account from your SharaForms connections settings.',
      'Choose that account in the form integration.',
      'Customize the message and delivery options.',
      'Run a test submission to verify the chat notification.',
    ],
    requirements: [
      'A Telegram account connected in SharaForms.',
      'A form with permission to edit integrations.',
      'Pro plan access for Telegram notifications.',
    ],
    capabilities: [
      'Account-based Telegram delivery without pasting raw webhooks.',
      'Custom message body and optional submission data blocks.',
      'Shared notification controls similar to Slack and Discord.',
    ],
    setupSteps: [
      {
        title: 'Connect Telegram first',
        body: 'Open your SharaForms user settings, go to Connections, and connect the Telegram account you want to use.',
      },
      {
        title: 'Pick the connected account on the form',
        body: 'In the Telegram integration, select the Telegram account from the available providers list.',
      },
      {
        title: 'Configure the alert content',
        body: 'Customize the notification message and choose whether to include submission answers, analytics, or quick links.',
      },
      {
        title: 'Submit a test entry',
        body: 'Send a sample response through the form and confirm it reaches the expected Telegram destination.',
      },
    ],
    tips: [
      'If the right account is missing, reconnect it from the Connections area before returning to the form.',
      'Keep the first line of the message meaningful for faster triage on mobile.',
    ],
  },
  webhook: {
    cardDescription: 'Post every submission to your own endpoint with optional HMAC signing and custom headers.',
    summary: 'Use the webhook integration to send SharaForms submissions to any API endpoint you control, with optional request signing and custom headers for downstream verification.',
    seoDescription: 'Configure SharaForms webhooks with a destination URL, optional HMAC signing secret, and custom headers.',
    steps: [
      'Create an endpoint that accepts POST requests.',
      'Add the webhook integration and paste the endpoint URL.',
      'Optionally add a signing secret and custom headers.',
      'Save the integration and inspect a test request.',
    ],
    requirements: [
      'A reachable HTTPS endpoint that accepts form submission payloads.',
      'A SharaForms form with integration access.',
      'Optional shared secret if you want signed verification.',
    ],
    capabilities: [
      'Direct POST delivery to your own backend or automation layer.',
      'Optional HMAC-SHA256 request signing using a webhook secret.',
      'Up to 10 custom headers for downstream authentication or routing.',
    ],
    setupSteps: [
      {
        title: 'Prepare your receiving endpoint',
        body: 'Make sure your endpoint can accept SharaForms submission requests and respond successfully.',
      },
      {
        title: 'Add the destination URL',
        body: 'Paste the endpoint into the Webhook URL field so SharaForms knows where to post each submission.',
      },
      {
        title: 'Configure advanced verification options',
        body: 'Open the Advanced section to add a webhook secret for HMAC signing and any custom headers required by your API.',
      },
      {
        title: 'Send a test submission and inspect the payload',
        body: 'Use a sample form submission to verify headers, signature handling, and response behavior before going live.',
      },
    ],
    tips: [
      'Use a long secret and verify the HMAC on your server before processing requests.',
      'Keep custom headers focused on authentication and routing metadata.',
      'Test failure handling so you know how your endpoint behaves under bad payloads or downtime.',
    ],
  },
  zapier: {
    cardDescription: 'Use Zapier as the automation layer between SharaForms and thousands of downstream apps.',
    summary: 'Connect SharaForms to Zapier when you want to trigger multi-step automations, enrich data, or forward submissions into the rest of your stack without writing code.',
    seoDescription: 'Connect SharaForms to Zapier and turn new submissions into automated workflows across your app stack.',
    steps: [
      'Open the SharaForms app inside Zapier.',
      'Connect your SharaForms account or webhook trigger.',
      'Choose the New Submission trigger and map your actions.',
      'Test the Zap and publish it.',
    ],
    requirements: [
      'A Zapier account.',
      'A SharaForms form that is ready to submit live data.',
      'A target workflow in Zapier for the downstream steps.',
    ],
    capabilities: [
      'Route submissions into CRM, email, spreadsheet, and task systems.',
      'Add multi-step automation beyond the built-in native integrations.',
      'Use Zapier filters, formatters, and branching around form responses.',
    ],
    setupSteps: [
      {
        title: 'Open the SharaForms integration in Zapier',
        body: 'Start from the SharaForms app page in Zapier and create a new Zap using the submission trigger you need.',
      },
      {
        title: 'Connect your SharaForms source',
        body: 'Authorize Zapier to access your SharaForms data or use the trigger flow Zapier expects for your form setup.',
      },
      {
        title: 'Map submission fields into downstream steps',
        body: 'Choose your follow-up apps and map the SharaForms fields into the actions that should run after each submission.',
      },
      {
        title: 'Test and publish the automation',
        body: 'Use a sample submission to verify the whole workflow, then publish the Zap once every step is returning the expected output.',
      },
    ],
    tips: [
      'Keep the first Zap simple, then add filters or branching after the trigger is stable.',
      'Label the Zap clearly with the form name so it is easy to maintain later.',
    ],
  },
  n8n: {
    cardDescription: 'Send form submissions to your n8n workflows using a simple webhook URL.',
    summary: 'Connect SharaForms to n8n by pasting your webhook URL. Every new submission is automatically sent to your n8n workflow for processing, transformations, and downstream automations.',
    seoDescription: 'Connect SharaForms to n8n with a webhook URL and automate actions for every form submission.',
    steps: [
      'Create a new workflow in n8n.',
      'Add a Webhook node as the trigger and copy the URL.',
      'Paste the webhook URL into the n8n integration settings in SharaForms.',
      'Activate the workflow in n8n and test with a live submission.',
    ],
    requirements: [
      'An n8n instance or n8n Cloud account.',
      'A SharaForms form ready to send submissions.',
    ],
    capabilities: [
      'Send every form submission directly to your n8n workflow.',
      'Use n8n to transform, filter, and route submission data to any connected app.',
      'Combine n8n\'s advanced branching and logic with SharaForms submissions.',
    ],
    setupSteps: [
      {
        title: 'Create a Webhook trigger in n8n',
        body: 'Open your n8n instance and create a new workflow. Add a Webhook node as the first step and set the HTTP method to POST.',
      },
      {
        title: 'Copy the webhook URL',
        body: 'Copy the Test URL for initial testing, or the Production URL once you activate the workflow. Paste this URL into the n8n integration settings in your SharaForms form.',
      },
      {
        title: 'Save and activate',
        body: 'Save the integration in SharaForms, then activate your n8n workflow. Your form submissions will now be sent to n8n automatically.',
      },
      {
        title: 'Test with a live submission',
        body: 'Submit your form once to verify the data arrives in n8n. Check the Webhook node output and adjust your downstream nodes as needed.',
      },
    ],
    tips: [
      'Use the Test URL during development — it shows the payload in n8n\'s editor for easy debugging.',
      'Switch to the Production URL once your workflow is live to handle real submissions.',
      'n8n expects JSON by default. SharaForms sends the submission as a POST with form_id, form_title, submission fields, and a data object keyed by field ID.',
    ],
  },
  notion: {
    cardDescription: 'Create Notion database pages from form submissions with automatic field mapping.',
    summary: 'Connect SharaForms to Notion to automatically create database pages for every submission. Map form fields to Notion properties and let each response populate the right columns.',
    seoDescription: 'Connect SharaForms to Notion and sync form submissions into a Notion database automatically.',
    steps: [
      'Connect your Notion workspace in SharaForms.',
      'Choose the database where submissions should land.',
      'Map form fields to database columns.',
      'Submit a test entry and confirm the row appears in Notion.',
    ],
    requirements: [
      'A Notion workspace where you can share databases with the SharaForms integration.',
      'A SharaForms form with integration access.',
      'Free plan or above.',
    ],
    capabilities: [
      'OAuth-based workspace connection instead of manual API tokens.',
      'Dynamic database and property discovery after connecting.',
      'Per-field column mapping so each form answer goes to the right property.',
    ],
    setupSteps: [
      {
        title: 'Connect your Notion workspace',
        body: 'From the Notion integration, click Connect Notion workspace. Authorize SharaForms to access the databases you want to use.',
      },
      {
        title: 'Select a database',
        body: 'After connecting, choose the target Notion database from the dropdown. You must share the database with the SharaForms integration inside Notion first.',
      },
      {
        title: 'Map form fields to database properties',
        body: 'Each form field gets a dropdown of available Notion properties. Match the fields you want to sync and leave the rest unmapped.',
      },
      {
        title: 'Test with a real submission',
        body: 'Submit the form and confirm a new page appears in your Notion database with the correct property values.',
      },
    ],
    tips: [
      'Make sure the database is shared with the SharaForms integration inside your Notion workspace settings.',
      'Property types in Notion must be compatible with your form field types (e.g. text fields to title/text properties).',
    ],
    providerDocsUrl: 'https://developers.notion.com/docs/working-with-databases',
    providerDocsLabel: 'Notion database docs',
  },
  microsoft_teams: {
    cardDescription: 'Post submission alerts into Microsoft Teams channels with a webhook URL.',
    summary: 'Connect Microsoft Teams to your form so every new submission sends a notification to a Teams channel. Paste your incoming webhook URL and customize the message content.',
    seoDescription: 'Connect SharaForms to Microsoft Teams with a webhook and send submission notifications to your channels.',
    steps: [
      'Create an incoming webhook in your Teams channel.',
      'Add the Microsoft Teams integration in your form.',
      'Paste the webhook URL and customize the notification message.',
      'Save and test with a live submission.',
    ],
    requirements: [
      'A Microsoft Teams channel where you can create incoming webhooks.',
      'A SharaForms form with integration access.',
      'Free plan or above.',
    ],
    capabilities: [
      'Webhook-based delivery directly into Teams channels.',
      'Customizable notification message with submission data.',
      'No middleware or code required.',
    ],
    setupSteps: [
      {
        title: 'Create an incoming webhook in Teams',
        body: 'Open your Teams channel settings, go to Connectors, and create an Incoming Webhook. Copy the generated URL.',
      },
      {
        title: 'Paste the webhook URL into SharaForms',
        body: 'Open the Microsoft Teams integration in your form and paste the webhook URL into the designated field.',
      },
      {
        title: 'Customize the notification',
        body: 'Edit the message content and decide whether to include submission fields, hidden fields, or quick links.',
      },
      {
        title: 'Test the delivery',
        body: 'Submit the form and confirm the notification appears in your Teams channel.',
      },
    ],
    tips: [
      'Create separate webhooks per channel when different forms notify different teams.',
      'Keep the message concise so it reads well on mobile.',
    ],
    providerDocsUrl: 'https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook',
    providerDocsLabel: 'Teams webhook docs',
  },
  google_chat: {
    cardDescription: 'Send submission notifications to Google Chat spaces with a webhook URL.',
    summary: 'Connect Google Chat to your form so each submission triggers a notification in your chat space. Paste your webhook URL and configure the message content.',
    seoDescription: 'Connect SharaForms to Google Chat with a webhook and send form submission notifications.',
    steps: [
      'Create a webhook in your Google Chat space.',
      'Add the Google Chat integration in your form.',
      'Paste the webhook URL and customize the message.',
      'Save and test with a live submission.',
    ],
    requirements: [
      'A Google Chat space where you can create webhooks.',
      'A SharaForms form with integration access.',
      'Free plan or above.',
    ],
    capabilities: [
      'Direct webhook delivery into Google Chat spaces.',
      'Customizable notification message with submission data.',
      'Simple setup without external middleware.',
    ],
    setupSteps: [
      {
        title: 'Create a webhook in Google Chat',
        body: 'Open your Google Chat space settings, find Apps & integrations, and create a webhook. Copy the generated URL.',
      },
      {
        title: 'Paste the webhook URL into SharaForms',
        body: 'Open the Google Chat integration in your form and paste the webhook URL into the designated field.',
      },
      {
        title: 'Customize the notification',
        body: 'Edit the message content and choose whether to include submission fields, hidden fields, or links.',
      },
      {
        title: 'Test the delivery',
        body: 'Submit the form and confirm the notification arrives in your Google Chat space.',
      },
    ],
    tips: [
      'Use separate webhooks for different forms or teams to keep channels organized.',
      'Google Chat webhooks support rich card formatting for structured messages.',
    ],
    providerDocsUrl: 'https://developers.google.com/chat/quickstart/webhooks',
    providerDocsLabel: 'Google Chat webhook docs',
  },
  ntfy: {
    cardDescription: 'Send push notifications to your phone or desktop with ntfy when a form is submitted.',
    summary: 'Use ntfy to receive instant push notifications for every SharaForms submission. Configure the topic URL, priority, and tags to control how notifications appear on your devices.',
    seoDescription: 'Connect SharaForms to ntfy and receive push notifications for every form submission.',
    steps: [
      'Choose or create an ntfy topic.',
      'Add the ntfy integration in your form.',
      'Paste the topic URL and configure priority and tags.',
      'Save and submit a test entry to verify the notification.',
    ],
    requirements: [
      'An ntfy topic URL (self-hosted or ntfy.sh).',
      'A SharaForms form with integration access.',
      'Free plan or above.',
    ],
    capabilities: [
      'Instant push notifications on phone or desktop.',
      'Configurable priority levels from min to max.',
      'Custom tags for emoji display and notification filtering.',
      'Clickable URL that opens your form or any destination.',
    ],
    setupSteps: [
      {
        title: 'Set up your ntfy topic',
        body: 'Choose a topic on ntfy.sh or your self-hosted instance. Install the ntfy app on your devices and subscribe to the topic.',
      },
      {
        title: 'Paste the topic URL into SharaForms',
        body: 'Open the ntfy integration in your form and paste the topic URL (e.g. https://ntfy.sh/mytopic).',
      },
      {
        title: 'Configure notification options',
        body: 'Set the priority level, add comma-separated tags for emoji or filtering, and optionally set a click URL.',
      },
      {
        title: 'Test with a real submission',
        body: 'Submit the form and confirm the push notification arrives on your devices.',
      },
    ],
    tips: [
      'Use higher priority levels for important forms so notifications break through Do Not Disturb.',
      'Tags like rocket or warning automatically map to emoji in ntfy notifications.',
    ],
    providerDocsUrl: 'https://docs.ntfy.sh/',
    providerDocsLabel: 'ntfy documentation',
  },
  airtable: {
    cardDescription: 'Create Airtable records from form submissions using a Personal Access Token.',
    summary: 'Connect SharaForms to Airtable so every submission creates a new record in your chosen base and table. Enter your API token, base ID, and table name to get started.',
    seoDescription: 'Connect SharaForms to Airtable and create records for every form submission automatically.',
    steps: [
      'Create a Personal Access Token in your Airtable account.',
      'Add the Airtable integration in your form.',
      'Enter your token, base ID, and table name.',
      'Save and test with a live submission.',
    ],
    requirements: [
      'An Airtable account (free or paid).',
      'A Personal Access Token with the right permissions (see setup step 1).',
      'Pro plan access for Airtable integrations.',
    ],
    capabilities: [
      'Automatic record creation for each form submission.',
      'Direct API integration without Zapier or middleware.',
      'Works with any Airtable base and table.',
    ],
    setupSteps: [
      {
        title: 'Create a Personal Access Token',
        body: 'Go to airtable.com/create/tokens. Click "Create new token", give it a name, and add these scopes: data.records:write and schema.bases:read. Then click "Create token" and copy it immediately — you won\'t see it again.',
      },
      {
        title: 'Find your Base ID',
        body: 'Open the Airtable base you want to use. Go to Help > API documentation (or visit airtable.com/app/YOUR_BASE/api). The Base ID is shown near the top and starts with "app". Copy it.',
      },
      {
        title: 'Find your Table name',
        body: 'The Table name is the exact name shown on the table tab at the bottom of your Airtable base (e.g. "Contacts", "Orders"). It is not the table ID.',
      },
      {
        title: 'Enter everything in SharaForms',
        body: 'Paste your Personal Access Token, Base ID, and Table name into the Airtable integration fields in your form.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new record appears in your Airtable table with the correct values.',
      },
    ],
    tips: [
      'Copy your token immediately after creating it — Airtable only shows it once.',
      'If the table has required fields, make sure your form sends data for them or the record creation will fail.',
    ],
    providerDocsUrl: 'https://airtable.com/developers/web/guides/create-a-base',
    providerDocsLabel: 'Airtable API docs',
  },
  trello: {
    cardDescription: 'Create Trello cards from form submissions with board and list selection.',
    summary: 'Connect SharaForms to Trello so every submission creates a new card in your chosen board and list. Use API credentials to dynamically load boards, lists, and labels.',
    seoDescription: 'Connect SharaForms to Trello and create cards for every form submission automatically.',
    steps: [
      'Get your Trello API key and token.',
      'Add the Trello integration in your form.',
      'Enter credentials, then select a board and list.',
      'Customize the card title and description, then test.',
    ],
    requirements: [
      'A Trello account.',
      'Pro plan access for Trello integrations.',
    ],
    capabilities: [
      'Dynamic loading of boards, lists, and labels after entering credentials.',
      'Customizable card title and description with field value mentions.',
      'Optional label assignment for organization.',
      'Automatic card creation on each form submission.',
    ],
    setupSteps: [
      {
        title: 'Get your Trello API key',
        body: 'Visit trello.com/power-ups/admin and select your Power-Up or create one. In the API key section, click "New" to generate your API key. Copy it.',
      },
      {
        title: 'Generate a Trello token',
        body: 'Below the API key, you\'ll see a link that says "Token" — click it. Trello will ask you to authorize the app. Click "Allow" and copy the token shown on the next page.',
      },
      {
        title: 'Enter credentials in SharaForms',
        body: 'Paste your API key and token into the Trello integration fields in your form. The board dropdown will load automatically once both are entered.',
      },
      {
        title: 'Select a board, list, and optional labels',
        body: 'Choose the target board and list for new cards. Optionally toggle labels to apply to each card.',
      },
      {
        title: 'Customize the card content',
        body: 'Use the title and description fields to define card content. Use @mentions to insert form field values into the card.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new card appears in your Trello list with the correct content.',
      },
    ],
    tips: [
      'Use @mentions in the card title to make submissions instantly scannable in your board.',
      'Labels help filter cards by category, priority, or form type.',
      'If the board dropdown doesn\'t load, double-check that both the API key and token are correct.',
    ],
    providerDocsUrl: 'https://developer.atlassian.com/cloud/trello/guides/rest-api/api-introduction/',
    providerDocsLabel: 'Trello API docs',
  },
  baserow: {
    cardDescription: 'Insert form submissions as rows into a Baserow table with column mapping.',
    summary: 'Connect SharaForms to Baserow so every submission creates a row in your chosen table. Load workspaces, databases, and tables dynamically, then map form fields to columns.',
    seoDescription: 'Connect SharaForms to Baserow and insert form submissions into your database tables automatically.',
    steps: [
      'Create a database token in your Baserow workspace.',
      'Add the Baserow integration in your form.',
      'Pick a workspace, database, and table.',
      'Map form fields to columns, save, and test.',
    ],
    requirements: [
      'A Baserow account (Baserow.io cloud or self-hosted).',
      'A database token with write access to the target table.',
      'Pro plan access for Baserow integrations.',
    ],
    capabilities: [
      'Dynamic loading of workspaces, databases, and tables after entering the token.',
      'Column mapping with automatic slugified names for unmapped fields.',
      'Supports self-hosted instances via a custom instance URL.',
      'Automatic row creation on each form submission.',
    ],
    setupSteps: [
      {
        title: 'Create a Baserow database token',
        body: 'In Baserow, open your workspace settings, go to Database tokens, and create a new token. Give it write permissions on the workspace that holds your table, then copy it.',
      },
      {
        title: 'Enter credentials in SharaForms',
        body: 'Paste the token into the Baserow integration fields in your form. If you self-host Baserow, also set your instance URL; otherwise leave it empty for baserow.io.',
      },
      {
        title: 'Select workspace, database, and table',
        body: 'Choose where submissions should land. The selectors load dynamically from your Baserow account once the token is entered.',
      },
      {
        title: 'Map form fields to columns',
        body: 'Match each form field to a Baserow column. Fields without an explicit mapping are stored under a slugified version of their field name.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new row appears in your Baserow table with the mapped values.',
      },
    ],
    tips: [
      'Create matching columns in your table first so mapped values always land correctly.',
      'Use single or long text columns for multi-select answers, which arrive as comma separated values.',
      'If selectors stay empty, check that the token has access to the workspace you expect.',
    ],
    providerDocsUrl: 'https://baserow.io/docs/api%2Fdatabase-rows-api',
    providerDocsLabel: 'Baserow API docs',
  },
  linear: {
    cardDescription: 'Create Linear issues from form submissions with team, project, and status selection.',
    summary: 'Connect SharaForms to Linear so every submission creates an issue in your chosen team. Customize titles and descriptions with form values, then route issues by project, status, and priority.',
    seoDescription: 'Connect SharaForms to Linear and create issues from form submissions automatically.',
    steps: [
      'Generate a personal API key in Linear settings.',
      'Add the Linear integration in your form.',
      'Select a team, then optionally a project, status, and priority.',
      'Customize the issue title and description, then test.',
    ],
    requirements: [
      'A Linear account.',
      'A personal API key with write access to issues.',
      'Pro plan access for Linear integrations.',
    ],
    capabilities: [
      'Dynamic loading of teams, projects, and statuses after entering the key.',
      'Issue title and description templates with field value mentions.',
      'Optional project, status, and priority assignment per submission.',
      'Submission data, analytics, and form links appended to descriptions.',
    ],
    setupSteps: [
      {
        title: 'Create a Linear API key',
        body: 'In Linear, go to Settings → Security and create a new personal API key under the API section. Copy the generated key.',
      },
      {
        title: 'Enter the key in SharaForms',
        body: 'Paste the API key into the Linear integration fields in your form. The team dropdown loads automatically.',
      },
      {
        title: 'Select a team and routing options',
        body: 'Choose the team that should receive issues. Optionally pick a project, status, and priority applied to every created issue.',
      },
      {
        title: 'Customize the issue content',
        body: 'Use the title and description fields to define issue content. Use @mentions to insert form field values into both.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new issue appears in your Linear team with the correct content and routing.',
      },
    ],
    tips: [
      'Include a mention of the email field in the description so assignees can follow up quickly.',
      'Use priorities to triage urgent feedback without opening Linear.',
      'Turn off unneeded links to keep descriptions short and focused.',
    ],
    providerDocsUrl: 'https://developers.linear.app/docs/graphql/working-with-the-graphql-api',
    providerDocsLabel: 'Linear API docs',
  },
  resend: {
    cardDescription: 'Send submission emails through Resend with custom sender, recipients, and content.',
    summary: 'Connect SharaForms to Resend so every submission sends an email through your own Resend account. Control the sender address, recipients, subject, and body, with optional submission summaries.',
    seoDescription: 'Send form submissions as emails through Resend automatically with SharaForms.',
    steps: [
      'Create an API key in your Resend dashboard.',
      'Verify your sending domain in Resend.',
      'Add the Resend integration in your form.',
      'Set the sender, recipients, subject, and body, then test.',
    ],
    requirements: [
      'A Resend account with an API key.',
      'A verified sending domain in Resend.',
      'Pro plan access for Resend integrations.',
    ],
    capabilities: [
      'Full control over the From address shown to recipients.',
      'Multiple recipients, one per line or comma separated.',
      'Subject and body templates with field value mentions.',
      'Optional auto-generated submission summary table.',
    ],
    setupSteps: [
      {
        title: 'Create a Resend API key',
        body: 'In Resend, open API Keys and create a key with sending permission. Copy it immediately; Resend only shows it once.',
      },
      {
        title: 'Verify your sending domain',
        body: 'In Resend Domains, add the domain you want to send from and complete the DNS verification so emails deliver reliably.',
      },
      {
        title: 'Enter credentials and addresses in SharaForms',
        body: 'Paste the API key, then fill in the From address on your verified domain and one or more recipients.',
      },
      {
        title: 'Write the subject and content',
        body: 'Use @mentions to insert form field values into the subject or body. Leave the body empty to send an auto-generated summary of the submission instead.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm the email arrives from your domain with the expected content.',
      },
    ],
    tips: [
      'Keep the From address on a verified domain or Resend will reject the send.',
      'Use reply-to when responses should reach a shared inbox rather than the sender.',
      'Combine a custom intro with the appended summary for concise handoff emails.',
    ],
    providerDocsUrl: 'https://resend.com/docs/api-reference/emails/send-email',
    providerDocsLabel: 'Resend API docs',
  },
  pipedrive: {
    cardDescription: 'Create Pipedrive deals from form submissions with contact person mapping.',
    summary: 'Connect SharaForms to Pipedrive so every submission creates a deal in your pipeline. Map contact name and email fields to create a linked person automatically.',
    seoDescription: 'Connect SharaForms to Pipedrive and create deals from form submissions automatically.',
    steps: [
      'Copy your API token from Pipedrive settings.',
      'Add the Pipedrive integration in your form.',
      'Optionally select a pipeline and stage.',
      'Map contact fields and customize the deal title, then test.',
    ],
    requirements: [
      'A Pipedrive account.',
      'An API token with write access.',
      'Pro plan access for Pipedrive integrations.',
    ],
    capabilities: [
      'Deal creation on each form submission with customizable titles.',
      'Automatic person creation from mapped name and email form fields.',
      'Pipeline and stage routing with deal value and currency support.',
      'Title templates with field value mentions.',
    ],
    setupSteps: [
      {
        title: 'Get your Pipedrive API token',
        body: 'In Pipedrive, open Settings → Personal preferences → API and copy your personal API token.',
      },
      {
        title: 'Enter the token in SharaForms',
        body: 'Paste the token into the Pipedrive integration fields in your form. Pipelines load automatically.',
      },
      {
        title: 'Select pipeline and stage',
        body: 'Optionally choose where new deals land inside your Pipedrive funnel.',
      },
      {
        title: 'Map contact fields and set deal options',
        body: 'Choose which form fields hold the contact name and email. Set an optional deal title template, value, and currency.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new deal appears in Pipedrive, linked to the created person.',
      },
    ],
    tips: [
      'Mapping at least the email field makes leads easy to deduplicate in Pipedrive.',
      'Use @mentions in the deal title, such as the company or service requested, for instant context.',
      'Leave pipeline empty to use your default Pipedrive funnel.',
    ],
    providerDocsUrl: 'https://developers.pipedrive.com/docs/api/v1',
    providerDocsLabel: 'Pipedrive API docs',
  },
  plane: {
    cardDescription: 'Create Plane issues from form submissions with project and state selection.',
    summary: 'Connect SharaForms to Plane so every submission creates an issue in your chosen project. Works with Plane Cloud and self-hosted instances, with title, description, state, and priority control.',
    seoDescription: 'Connect SharaForms to Plane and create issues from form submissions automatically.',
    steps: [
      'Generate an API token in your Plane workspace settings.',
      'Add the Plane integration in your form.',
      'Select a workspace and project, then optionally a state and priority.',
      'Customize the issue title and description, then test.',
    ],
    requirements: [
      'A Plane workspace (Plane Cloud or self-hosted).',
      'An API token with write access.',
      'Pro plan access for Plane integrations.',
    ],
    capabilities: [
      'Dynamic loading of workspaces, projects, and states after entering the token.',
      'Issue title and description templates with field value mentions.',
      'Optional state and priority assignment per submission.',
      'Support for self-hosted Plane instances via a custom instance URL.',
    ],
    setupSteps: [
      {
        title: 'Generate a Plane API token',
        body: 'In Plane, open Settings → API Tokens and create a new token. Copy it now; tokens may only be shown once.',
      },
      {
        title: 'Enter credentials in SharaForms',
        body: 'Paste the token into the Plane integration fields in your form. If you self-host Plane, also set your instance URL; otherwise leave it empty for Plane Cloud.',
      },
      {
        title: 'Select workspace and project',
        body: 'Choose where new issues should be created. Projects load dynamically from your workspace.',
      },
      {
        title: 'Configure the issue content',
        body: 'Optionally pick a state and priority, then customize the title and description with @mentions for form field values.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new issue appears in your Plane project with the correct content.',
      },
    ],
    tips: [
      'Route different forms to different projects by creating one integration per form.',
      'Keep the description template short and let the appended submission data carry the details.',
      'For self-hosted Plane, make sure SharaForms can reach your instance API URL over HTTPS.',
    ],
    providerDocsUrl: 'https://developers.plane.so/api-reference/issues/create-issue',
    providerDocsLabel: 'Plane API docs',
  },
  supabase: {
    cardDescription: 'Insert form submissions into a Supabase table with dynamic column mapping.',
    summary: 'Connect SharaForms to Supabase so every submission creates a row in your database. Load tables and columns dynamically, map form fields, and configure column types.',
    seoDescription: 'Connect SharaForms to Supabase and insert form submissions into your database tables automatically.',
    steps: [
      'Find your Supabase project URL and API key.',
      'Add the Supabase integration in your form.',
      'Select a table, map form fields to columns, and set types.',
      'Save and test with a live submission.',
    ],
    requirements: [
      'A Supabase project with at least one table.',
      'An API key from your project (see setup step 1).',
      'Pro plan access for Supabase integrations.',
    ],
    capabilities: [
      'Dynamic table and column discovery after entering credentials.',
      'Per-field column mapping with explicit type casting.',
      'Supports text, integer, float, boolean, JSON, date, timestamp, and array types.',
      'Row-Level Security compatible with anon key.',
    ],
    setupSteps: [
      {
        title: 'Find your project URL and API key',
        body: 'Open your Supabase dashboard and select your project. Go to Project Settings (gear icon) > API. Copy the "Project URL" and either the "anon public" key or the "service_role" key.',
      },
      {
        title: 'Choose the right API key',
        body: 'Use the "anon public" key if your table has Row-Level Security (RLS) policies that allow inserts — this is the most secure option. Use "service_role" only if RLS is disabled or you need to bypass it. The service_role key has full access, so keep it secret.',
      },
      {
        title: 'Enter credentials in SharaForms',
        body: 'Paste your project URL and API key into the Supabase integration fields. The table dropdown will load automatically once both are entered.',
      },
      {
        title: 'Select a table and map columns',
        body: 'Choose the target table. Each form field gets a dropdown of available columns. Map the fields you want to sync to the right columns.',
      },
      {
        title: 'Set column types',
        body: 'For each mapped column, select the data type that matches (Text, Integer, Boolean, JSON, etc.). This ensures the data is inserted correctly into your Supabase table.',
      },
      {
        title: 'Test with a submission',
        body: 'Submit the form and confirm a new row appears in your Supabase table with the correct values and types.',
      },
    ],
    tips: [
      'The anon key with RLS is the recommended approach for production — it limits what the integration can access.',
      'If a column in your table is NOT NULL, make sure your form sends data for it or the insert will fail.',
      'Use JSON type for complex or nested data that doesn\'t fit into a single text or number column.',
    ],
    providerDocsUrl: 'https://supabase.com/docs/guides/api',
    providerDocsLabel: 'Supabase API docs',
  },
  google_sheets: {
    cardDescription: 'Sync every form submission into Google Sheets using a connected Google account.',
    summary: 'Use the Google Sheets integration to capture every new SharaForms submission in a spreadsheet so your team can review, filter, and share submission data in a familiar format.',
    seoDescription: 'Connect Google Sheets to SharaForms and sync form submissions into a spreadsheet automatically.',
    steps: [
      'Connect your Google account in SharaForms.',
      'Choose that account in the Google Sheets integration.',
      'Save the integration and let the sheet connection initialize.',
      'Submit the form once and verify the spreadsheet entry.',
    ],
    requirements: [
      'A Google account with the required Sheets and Drive permissions.',
      'A SharaForms form with integration access.',
      'Free plan or above.',
    ],
    capabilities: [
      'Automatic spreadsheet entry creation for each submission.',
      'OAuth-based Google account connection instead of raw credentials.',
      'Easy handoff into review, exports, formulas, or downstream reporting workflows.',
    ],
    setupSteps: [
      {
        title: 'Connect Google in SharaForms',
        body: 'If your Google account is not connected yet, use the integration or Connections area to authorize Google with the required file permissions.',
      },
      {
        title: 'Select the Google account on the form',
        body: 'In the Google Sheets integration, pick the connected Google account that should own the spreadsheet workflow.',
      },
      {
        title: 'Wait for the spreadsheet link to become available',
        body: 'After saving, SharaForms initializes the Sheets connection. Once ready, the integration shows a direct Open button to the spreadsheet.',
      },
      {
        title: 'Validate with a sample response',
        body: 'Submit the form once and confirm the row appears in Google Sheets with the values you expect.',
      },
    ],
    tips: [
      'Reconnect the Google account if the required Drive scope is missing.',
      'Use the sheet as an operational log, then connect it to dashboards or formulas if needed.',
    ],
  },
}

export const generalIntegrationGuides = [
  {
    title: 'Native Integrations',
    description: 'Built directly into SharaForms for the fastest setup.',
    steps: [
      'Open a form and go to <b>Integrations</b>.',
      'Choose the native integration you want to enable.',
      'Add the required account, webhook, or destination details.',
      'Save the integration and verify it with a real form submission.',
    ],
  },
  {
    title: 'Connected Accounts',
    description: 'For integrations that rely on OAuth providers such as Google or Telegram.',
    steps: [
      'Connect the provider account from SharaForms <b>Connections</b> or directly from the integration.',
      'Return to the form and select the connected account.',
      'Confirm the provider still has the required permissions or scopes.',
      'Test with a real submission before sharing the form publicly.',
    ],
  },
  {
    title: 'External Automation Platforms',
    description: 'For Zapier, n8n, and other automation platforms.',
    steps: [
      'Open the provider page and start a new workflow with SharaForms as the trigger.',
      'Connect the source form or submission event.',
      'Map the SharaForms fields into the downstream actions.',
      'Publish only after a complete end-to-end test succeeds.',
    ],
  },
]

function buildIntegrationGuide(slug) {
  const integration = integrationsCatalog[slug]
  const content = guideContent[slug]

  if (!integration || !content) {
    return null
  }

  return {
    ...integration,
    slug,
    tierLabel: tierLabels[integration.required_tier] ?? 'Plan based',
    ...content,
  }
}

export const integrationGuides = Object.keys(integrationsCatalog)
  .map(buildIntegrationGuide)
  .filter(Boolean)

export function getIntegrationGuide(slug) {
  return integrationGuides.find((guide) => guide.slug === slug || guide.slug === slug.replace(/-/g, '_')) ?? null
}
