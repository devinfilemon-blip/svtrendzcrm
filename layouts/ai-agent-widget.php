<?php if (isset($_SESSION['user_id'])) : ?>
<link href="assets/css/crm-ai-agent.css?v=20250801a" rel="stylesheet" type="text/css" />
<div id="crmAiRoot" class="crm-ai-root" aria-live="polite">
    <button type="button" id="crmAiFab" class="crm-ai-fab" title="AI Assistant" aria-label="Open AI Assistant">
        <i class="bx bx-bot"></i>
        <span class="crm-ai-fab-pulse"></span>
    </button>

    <div id="crmAiPanel" class="crm-ai-panel" hidden>
        <div class="crm-ai-panel-head">
            <div class="crm-ai-panel-title">
                <i class="bx bx-bot"></i>
                <div>
                    <strong>infiCRM AI</strong>
                    <small>Gemini · Ask, create with confirm</small>
                </div>
            </div>
            <div class="crm-ai-panel-actions">
                <button type="button" id="crmAiClear" class="crm-ai-icon-btn" title="Clear chat"><i class="bx bx-trash"></i></button>
                <button type="button" id="crmAiClose" class="crm-ai-icon-btn" title="Close"><i class="bx bx-x"></i></button>
            </div>
        </div>
        <div id="crmAiMessages" class="crm-ai-messages"></div>
        <div class="crm-ai-suggestions" id="crmAiSuggestions">
            <button type="button" data-prompt="What are my follow-ups and reminders today?">Today's work</button>
            <button type="button" data-prompt="Show my won projects and open tasks">Projects</button>
            <button type="button" data-prompt="Summarize today's daily reports">Daily reports</button>
            <button type="button" data-prompt="Help me submit my daily report">Add daily report</button>
            <button type="button" data-prompt="Help me create a new lead">Add lead</button>
        </div>
        <form id="crmAiForm" class="crm-ai-form" autocomplete="off">
            <textarea id="crmAiInput" rows="1" placeholder="Ask about leads, or say create a lead/reminder..."></textarea>
            <button type="submit" id="crmAiSend" title="Send"><i class="bx bx-send"></i></button>
        </form>
    </div>
</div>
<script src="assets/js/crm-ai-agent.js?v=20250801d"></script>
<?php endif; ?>
