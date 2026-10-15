<?php
$pageTitle  = 'System Settings';
$activePage = 'settings';
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db  = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_institution') {
        $updates = [
            'institution_name'    => trim($_POST['institution_name']    ?? ''),
            'institution_acronym' => trim($_POST['institution_acronym'] ?? ''),
            'institution_address' => trim($_POST['institution_address'] ?? ''),
        ];
        $stmt = $db->prepare("INSERT INTO system_config (config_key,config_value) VALUES (?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)");
        foreach ($updates as $k => $v) $stmt->execute([$k, $v]);
        auditLog('updated','config','institution',null,$updates,'Institution info updated');
        $msg = 'Institution information saved.';
    }

    if ($action === 'save_qet') {
        $q = (int)($_POST['q'] ?? 40);
        $e = (int)($_POST['e'] ?? 40);
        $t = (int)($_POST['t'] ?? 20);
        if ($q + $e + $t !== 100) { $msg = 'Q+E+T weights must sum to exactly 100%.'; $msgType = 'danger'; }
        else {
            $stmt = $db->prepare("INSERT INTO system_config (config_key,config_value) VALUES (?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)");
            $stmt->execute(['qet_q_weight', $q]);
            $stmt->execute(['qet_e_weight', $e]);
            $stmt->execute(['qet_t_weight', $t]);
            auditLog('updated','config','qet_weights',null,['q'=>$q,'e'=>$e,'t'=>$t],'Q/E/T weights updated');
            $msg = 'Q/E/T weights saved.';
        }
    }

    if ($action === 'save_scale') {
        $values  = $_POST['scale_value']    ?? [];
        $labels  = $_POST['scale_label']    ?? [];
        $colors  = $_POST['scale_color']    ?? [];
        $minScores = $_POST['scale_min']    ?? [];
        $scale   = [];
        for ($i = 0; $i < count($labels); $i++) {
            if (trim($labels[$i]) === '') continue;
            $scale[] = [
                'value'     => (float)($values[$i] ?? $i+1),
                'label'     => trim($labels[$i]),
                'color'     => $colors[$i]  ?? '#6c757d',
                'min_score' => (float)($minScores[$i] ?? 0),
            ];
        }
        $stmt = $db->prepare("INSERT INTO system_config (config_key,config_value) VALUES (?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)");
        $stmt->execute(['rating_scale', json_encode($scale)]);
        auditLog('updated','config','rating_scale',null,$scale,'Rating scale updated');
        $msg = 'Rating scale saved.';
    }

    if ($action === 'save_workflow') {
        $selfRating   = isset($_POST['allow_self_rating'])       ? '1' : '0';
        $adminApproval = isset($_POST['require_admin_approval']) ? '1' : '0';
        $stmt = $db->prepare("INSERT INTO system_config (config_key,config_value) VALUES (?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)");
        $stmt->execute(['allow_self_rating',      $selfRating]);
        $stmt->execute(['require_admin_approval', $adminApproval]);
        $msg = 'Workflow settings saved.';
    }

    if ($action === 'save_ai') {
        $aiProvider = trim($_POST['ai_provider'] ?? 'gemini');
        $geminiKey  = trim($_POST['gemini_api_key'] ?? '');
        $openrouterKey = trim($_POST['openrouter_api_key'] ?? '');
        $aiMode     = trim($_POST['ai_verification_mode'] ?? 'advisory');
        
        // Validate AI mode - must be one of the three valid options
        if (!in_array($aiMode, ['disabled', 'advisory', 'strict'])) {
            $aiMode = 'advisory'; // Force default if invalid
        }
        
        // Validate AI provider
        if (!in_array($aiProvider, ['gemini', 'openrouter'])) {
            $aiProvider = 'gemini';
        }
        
        $stmt = $db->prepare("INSERT INTO system_config (config_key,config_value) VALUES (?,?) ON DUPLICATE KEY UPDATE config_value=VALUES(config_value)");
        $stmt->execute(['ai_provider', $aiProvider]);
        $stmt->execute(['gemini_api_key', $geminiKey]);
        $stmt->execute(['openrouter_api_key', $openrouterKey]);
        $stmt->execute(['ai_verification_mode', $aiMode]);
        
        auditLog('updated','config','ai_settings',null,[
            'provider'=>$aiProvider,
            'gemini_key'=>$geminiKey?'[set]':'[empty]',
            'openrouter_key'=>$openrouterKey?'[set]':'[empty]',
            'mode'=>$aiMode
        ],'AI settings updated');
        
        $msg = 'AI settings saved. Provider: ' . strtoupper($aiProvider) . ', Mode: ' . $aiMode . '.';
    }
}

// Load config
function cfg(PDO $db, string $key, string $default = ''): string {
    $r = $db->prepare("SELECT config_value FROM system_config WHERE config_key=?");
    $r->execute([$key]);
    return $r->fetchColumn() ?: $default;
}
$instName    = cfg($db,'institution_name','Isabela State University - Ilagan Campus');
$instAcronym = cfg($db,'institution_acronym','ISU');
$instAddress = cfg($db,'institution_address','');
$qW = cfg($db,'qet_q_weight','40');
$eW = cfg($db,'qet_e_weight','40');
$tW = cfg($db,'qet_t_weight','20');
$selfRating   = cfg($db,'allow_self_rating','0');
$adminApproval= cfg($db,'require_admin_approval','1');
$scaleJson    = cfg($db,'rating_scale','');
$scale = $scaleJson ? json_decode($scaleJson, true) : [
    ['value'=>5,'label'=>'Outstanding',      'color'=>'#198754','min_score'=>4.5],
    ['value'=>4,'label'=>'Very Satisfactory','color'=>'#0d6efd','min_score'=>3.5],
    ['value'=>3,'label'=>'Satisfactory',     'color'=>'#ffc107','min_score'=>2.5],
    ['value'=>2,'label'=>'Unsatisfactory',   'color'=>'#fd7e14','min_score'=>1.5],
    ['value'=>1,'label'=>'Poor',             'color'=>'#dc3545','min_score'=>0.0],
];
?>

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="bi bi-gear me-2"></i>System Settings</h1>
    <p class="text-muted mb-0 mt-1" style="font-size: 0.9rem;">Configure system-wide settings, email notifications, and AI features</p>
  </div>
</div>

<?php if($msg): ?><div class="alert alert-<?= $msgType ?> mb-4"><?= h($msg) ?></div><?php endif; ?>

<div class="row g-4">
  <!-- Institution Info -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h6><i class="bi bi-building me-2"></i>Institution Information</h6></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="save_institution">
          <div class="mb-3"><label class="form-label">Institution Name</label>
            <input class="form-control" name="institution_name" value="<?= h($instName) ?>"></div>
          <div class="mb-3"><label class="form-label">Acronym</label>
            <input class="form-control" name="institution_acronym" value="<?= h($instAcronym) ?>"></div>
          <div class="mb-3"><label class="form-label">Address</label>
            <input class="form-control" name="institution_address" value="<?= h($instAddress) ?>"></div>
          <button class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Q/E/T Weights -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h6><i class="bi bi-sliders me-2"></i>Q/E/T Rating Weights</h6></div>
      <div class="card-body">
        <p class="text-muted small">Percentage weights for Quality, Efficiency, and Timeliness. Must sum to 100%.</p>
        <form method="POST" onsubmit="return validateQET()">
          <input type="hidden" name="action" value="save_qet">
          <div class="row g-3 mb-3">
            <div class="col-4"><label class="form-label">Quality (Q) %</label>
              <input type="number" class="form-control qet-input" name="q" id="qet-q" value="<?= h($qW) ?>" min="0" max="100"></div>
            <div class="col-4"><label class="form-label">Efficiency (E) %</label>
              <input type="number" class="form-control qet-input" name="e" id="qet-e" value="<?= h($eW) ?>" min="0" max="100"></div>
            <div class="col-4"><label class="form-label">Timeliness (T) %</label>
              <input type="number" class="form-control qet-input" name="t" id="qet-t" value="<?= h($tW) ?>" min="0" max="100"></div>
          </div>
          <div id="qet-sum-msg" class="mb-3"></div>
          <button class="btn btn-primary">Save Weights</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Rating Scale -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6><i class="bi bi-star me-2"></i>Rating Scale</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addScaleRow()">+ Add Level</button>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="save_scale">
          <div class="table-container mb-3">
            <table class="ipcr-table" id="scale-table">
              <thead><tr><th>Value</th><th>Label</th><th>Min Score</th><th>Color</th><th></th></tr></thead>
              <tbody id="scale-tbody">
                <?php foreach($scale as $level): ?>
                <tr>
                  <td><input type="number" class="form-control form-control-sm" name="scale_value[]" value="<?= h($level['value']) ?>" style="width:60px"></td>
                  <td><input class="form-control form-control-sm" name="scale_label[]" value="<?= h($level['label']) ?>"></td>
                  <td><input type="number" step="0.001" class="form-control form-control-sm" name="scale_min[]" value="<?= h($level['min_score']) ?>"></td>
                  <td><input type="color" class="form-control form-control-color form-control-sm" name="scale_color[]" value="<?= h($level['color']) ?>" style="width:50px;height:32px"></td>
                  <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button class="btn btn-primary">Save Rating Scale</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Workflow -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h6><i class="bi bi-diagram-3 me-2"></i>Workflow Options</h6></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="save_workflow">
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="allow_self_rating" id="self-rating" <?= $selfRating==='1'?'checked':'' ?>>
            <label class="form-check-label" for="self-rating">Enable self-rating by Faculty</label>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="require_admin_approval" id="admin-approval" <?= $adminApproval==='1'?'checked':'' ?>>
            <label class="form-check-label" for="admin-approval">Require Admin approval before finalization</label>
          </div>
          <button class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
  </div>

  <!-- AI Verification Settings -->
  <div class="col-12">
    <div class="card" style="border-top:3px solid #7c3aed">
      <div class="card-header" style="background:linear-gradient(90deg,#f5f3ff,#ede9fe)">
        <h6 style="color:#7c3aed"><i class="bi bi-stars me-2"></i>AI Evidence Verification</h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">
          AI automatically checks uploaded evidence files and verifies if they match the expected document type for each success indicator.
        </p>
        
        <form method="POST">
          <input type="hidden" name="action" value="save_ai">
          
          <!-- AI Provider Selection -->
          <div class="mb-4">
            <label class="form-label fw-bold">AI Provider</label>
            <?php $aiProvider = cfg($db,'ai_provider','gemini'); ?>
            
            <div class="row g-3">
              <!-- Gemini Option -->
              <div class="col-md-6">
                <div class="form-check card p-3 h-100 <?= $aiProvider==='gemini'?'border-primary':'' ?>" style="cursor:pointer" onclick="document.getElementById('provider-gemini').click()">
                  <input class="form-check-input" type="radio" name="ai_provider" id="provider-gemini" value="gemini" <?= $aiProvider==='gemini'?'checked':'' ?> onchange="toggleProviderFields()">
                  <label class="form-check-label" for="provider-gemini" style="cursor:pointer">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <strong>Google Gemini</strong>
                      <span class="badge bg-warning text-dark" style="font-size:.65rem">REQUIRES BILLING</span>
                    </div>
                    <div class="text-muted small">
                      ✓ Direct access to Google Gemini<br>
                      ✓ Fast and reliable<br>
                      ⚠️ Free tier has limited access to generateContent<br>
                      ⚠️ Requires billing setup for full access
                    </div>
                  </label>
                </div>
              </div>
              
              <!-- OpenRouter Option -->
              <div class="col-md-6">
                <div class="form-check card p-3 h-100 <?= $aiProvider==='openrouter'?'border-primary':'' ?>" style="cursor:pointer" onclick="document.getElementById('provider-openrouter').click()">
                  <input class="form-check-input" type="radio" name="ai_provider" id="provider-openrouter" value="openrouter" <?= $aiProvider==='openrouter'?'checked':'' ?> onchange="toggleProviderFields()">
                  <label class="form-check-label" for="provider-openrouter" style="cursor:pointer">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <strong>OpenRouter</strong>
                      <span class="badge bg-success" style="font-size:.65rem">100% FREE</span>
                    </div>
                    <div class="text-muted small">
                      ✓ <strong>Totally FREE</strong> - No billing required<br>
                      ✓ 200 requests/day free tier<br>
                      ✓ Access to Gemini + GPT + Claude<br>
                      ✓ Easy setup - just sign up and get key
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Gemini API Key Field -->
          <div class="mb-3" id="gemini-key-field" style="display:<?= $aiProvider==='gemini'?'block':'none' ?>">
            <label class="form-label fw-bold">
              <i class="bi bi-google me-1"></i>Google Gemini API Key
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control" name="gemini_api_key" id="gemini-key"
                value="<?= h(cfg($db,'gemini_api_key','')) ?>"
                placeholder="AIza... (Get from Google AI Studio)">
              <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('gemini-key')">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">
              <i class="bi bi-info-circle me-1"></i>
              Get your free key at <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a><br>
              <small class="text-warning">⚠️ Note: Free tier has limited access. Enable billing for full access.</small>
            </div>
          </div>

          <!-- OpenRouter API Key Field -->
          <div class="mb-3" id="openrouter-key-field" style="display:<?= $aiProvider==='openrouter'?'block':'none' ?>">
            <label class="form-label fw-bold">
              <i class="bi bi-lightning-charge me-1"></i>OpenRouter API Key
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control" name="openrouter_api_key" id="openrouter-key"
                value="<?= h(cfg($db,'openrouter_api_key','')) ?>"
                placeholder="sk-or-v1-... (Get from OpenRouter)">
              <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('openrouter-key')">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">
              <i class="bi bi-info-circle me-1"></i>
              <strong>Quick Setup (5 minutes):</strong><br>
              1. Go to <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a><br>
              2. Sign in with Google (free)<br>
              3. Click "Create Key" and copy it<br>
              4. Paste here and save<br>
              <small class="text-success">✓ 100% FREE - No billing required, 200 requests/day</small>
            </div>
          </div>

          <hr class="my-4">
          
          <!-- AI Verification Mode -->
          <div class="mb-4">
            <label class="form-label fw-bold">AI Verification Mode</label>
            <?php $aiMode = cfg($db,'ai_verification_mode','advisory'); ?>
            
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="ai_verification_mode" id="ai-disabled" value="disabled" <?= $aiMode==='disabled'?'checked':'' ?>>
              <label class="form-check-label" for="ai-disabled">
                <strong>Disabled</strong> — No AI verification (all uploads allowed)
                <div class="text-muted small">Use this if you don't have an API key or want to disable AI checks</div>
              </label>
            </div>
            
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="ai_verification_mode" id="ai-advisory" value="advisory" <?= $aiMode==='advisory'?'checked':'' ?>>
              <label class="form-check-label" for="ai-advisory">
                <strong>Advisory Only</strong> (Recommended) — AI checks but never blocks uploads
                <div class="text-muted small">Shows AI confidence and warnings, but always allows upload regardless of score</div>
              </label>
            </div>
            
            <div class="form-check">
              <input class="form-check-input" type="radio" name="ai_verification_mode" id="ai-strict" value="strict" <?= $aiMode==='strict'?'checked':'' ?>>
              <label class="form-check-label" for="ai-strict">
                <strong>Strict Mode</strong> — AI blocks uploads below 50% confidence
                <div class="text-muted small">⚠️ Warning: May reject valid files. Use only if you need strict validation.</div>
              </label>
            </div>
          </div>

          <?php
          $geminiKey = cfg($db,'gemini_api_key','');
          $openrouterKey = cfg($db,'openrouter_api_key','');
          $hasGemini = !empty($geminiKey);
          $hasOpenRouter = !empty($openrouterKey);
          $currentProvider = cfg($db,'ai_provider','gemini');
          $hasActiveKey = ($currentProvider === 'gemini' && $hasGemini) || ($currentProvider === 'openrouter' && $hasOpenRouter);
          ?>
          
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <button class="btn btn-primary">
              <i class="bi bi-save me-1"></i>Save AI Settings
            </button>
            
            <?php if($hasActiveKey): ?>
              <span class="badge" style="background:#16a34a;font-size:.75rem;padding:6px 12px">
                <i class="bi bi-check-circle me-1"></i><?= strtoupper($currentProvider) ?> Key Configured
              </span>
            <?php else: ?>
              <span class="badge bg-warning text-dark" style="font-size:.75rem;padding:6px 12px">
                <i class="bi bi-exclamation-triangle me-1"></i>No API Key for <?= strtoupper($currentProvider) ?>
              </span>
            <?php endif; ?>
            
            <?php if($hasGemini && $currentProvider !== 'gemini'): ?>
              <span class="badge bg-secondary" style="font-size:.7rem;padding:4px 8px">
                <i class="bi bi-key-fill me-1"></i>Gemini key stored
              </span>
            <?php endif; ?>
            
            <?php if($hasOpenRouter && $currentProvider !== 'openrouter'): ?>
              <span class="badge bg-secondary" style="font-size:.7rem;padding:4px 8px">
                <i class="bi bi-key-fill me-1"></i>OpenRouter key stored
              </span>
            <?php endif; ?>
          </div>
        </form>

        <!-- Info Box -->
        <div class="alert alert-info mt-4 mb-0" style="border-left:4px solid #3b82f6">
          <div class="d-flex gap-2">
            <i class="bi bi-lightbulb-fill" style="color:#3b82f6"></i>
            <div style="font-size:.85rem">
              <strong>Recommendation:</strong> Use <strong>OpenRouter</strong> for 100% free AI verification with no billing required.<br>
              Both providers use the same AI models and work identically in your system.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.qet-input').forEach(i=>i.addEventListener('input',updateQETSum));
function updateQETSum(){
  const q=+document.getElementById('qet-q').value||0;
  const e=+document.getElementById('qet-e').value||0;
  const t=+document.getElementById('qet-t').value||0;
  const sum=q+e+t;
  const el=document.getElementById('qet-sum-msg');
  el.innerHTML=`<span class="badge ${sum===100?'bg-success':'bg-danger'}">Total: ${sum}%</span>${sum!==100?' <small class="text-danger">Must equal 100%</small>':''}`;
}
function validateQET(){
  const q=+document.getElementById('qet-q').value||0;
  const e=+document.getElementById('qet-e').value||0;
  const t=+document.getElementById('qet-t').value||0;
  if(q+e+t!==100){alert('Q+E+T must sum to 100%.');return false;}
  return true;
}
function addScaleRow(){
  const tbody=document.getElementById('scale-tbody');
  const tr=document.createElement('tr');
  tr.innerHTML=`<td><input type="number" class="form-control form-control-sm" name="scale_value[]" value="" style="width:60px"></td>
  <td><input class="form-control form-control-sm" name="scale_label[]" value=""></td>
  <td><input type="number" step="0.001" class="form-control form-control-sm" name="scale_min[]" value="0"></td>
  <td><input type="color" class="form-control form-control-color form-control-sm" name="scale_color[]" value="#6c757d" style="width:50px;height:32px"></td>
  <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>`;
  tbody.appendChild(tr);
}
function toggleProviderFields(){
  const provider = document.querySelector('input[name="ai_provider"]:checked').value;
  document.getElementById('gemini-key-field').style.display = provider === 'gemini' ? 'block' : 'none';
  document.getElementById('openrouter-key-field').style.display = provider === 'openrouter' ? 'block' : 'none';
}
function togglePassword(fieldId){
  const field = document.getElementById(fieldId);
  field.type = field.type === 'password' ? 'text' : 'password';
}
updateQETSum();
toggleProviderFields();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
