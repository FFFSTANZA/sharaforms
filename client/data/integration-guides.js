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
    cardDescription: 'Run SharaForms automations in n8n when you want more control over self-hosted or advanced workflows.',
    summary: 'Use n8n with SharaForms to build flexible automation pipelines, transform submission payloads, and send data into internal systems or third-party services.',
    seoDescription: 'Set up SharaForms with n8n to build custom automation workflows for every form submission.',
    steps: [
      'Open the SharaForms trigger in n8n.',
      'Create or import the workflow for your use case.',
      'Connect the trigger to your downstream nodes.',
      'Test the workflow with live submissions and activate it.',
    ],
    requirements: [
      'An n8n instance or n8n Cloud account.',
      'A SharaForms form ready to emit production data.',
      'A target workflow that processes new submissions.',
    ],
    capabilities: [
      'Build advanced branching, data transformation, and internal-service workflows.',
      'Use n8n as a more customizable automation layer than simple point integrations.',
      'Keep control over the workflow URL and provider-side edits.',
    ],
    setupSteps: [
      {
        title: 'Start from the SharaForms trigger in n8n',
        body: 'Create a new n8n workflow and add the SharaForms trigger or integration template as the first node.',
      },
      {
        title: 'Configure the follow-up nodes',
        body: 'Add the apps, webhooks, database writes, or internal processes that should run after the submission enters n8n.',
      },
      {
        title: 'Connect the workflow back to your form flow',
        body: 'Complete the connection steps required by n8n so new SharaForms submissions reach the active workflow.',
      },
      {
        title: 'Run an end-to-end test',
        body: 'Submit the form, inspect the workflow execution in n8n, and activate it only after the full chain behaves correctly.',
      },
    ],
    tips: [
      'Version your workflow before large changes so you can roll back quickly.',
      'Use dedicated error paths in n8n for failed downstream actions.',
    ],
  },
  activepieces: {
    cardDescription: 'Use Activepieces for no-code automations that start from SharaForms submissions.',
    summary: 'Connect SharaForms with Activepieces to route new submissions into automations, notifications, internal processes, and other app integrations managed in a no-code builder.',
    seoDescription: 'Set up SharaForms with Activepieces and automate actions for every new form submission.',
    steps: [
      'Open the SharaForms piece in Activepieces.',
      'Create the flow and connect your trigger.',
      'Map submission fields into the downstream steps.',
      'Test the flow and publish it.',
    ],
    requirements: [
      'An Activepieces account or workspace.',
      'A SharaForms form ready for real traffic.',
      'A flow design for what should happen after each submission.',
    ],
    capabilities: [
      'Build no-code automations on top of SharaForms triggers.',
      'Send responses into follow-up tools, notifications, and internal operations.',
      'Keep workflow editing on the provider side while using SharaForms as the data source.',
    ],
    setupSteps: [
      {
        title: 'Open the SharaForms piece in Activepieces',
        body: 'Create a new flow in Activepieces and choose the SharaForms integration as your trigger or starting point.',
      },
      {
        title: 'Design the flow around your submission data',
        body: 'Add the actions that should run after each submission and map the incoming fields into those steps.',
      },
      {
        title: 'Complete the provider-side connection',
        body: 'Follow the Activepieces connection flow so the piece can receive data from the form correctly.',
      },
      {
        title: 'Validate with a live test submission',
        body: 'Run the full flow using a real submission and check each step before publishing the automation.',
      },
    ],
    tips: [
      'Start with a narrow flow, then expand once the base trigger is stable.',
      'Keep your field naming clear inside SharaForms so mapping stays manageable in Activepieces.',
    ],
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
    description: 'For Zapier, n8n, Activepieces, and similar workflow builders.',
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
