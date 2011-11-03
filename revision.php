<?php

// include_once($_SERVER['DOCUMENT_ROOT'] . '/assets/plugins/revision/revision.php');

$event = & $modx->Event;

define('DOCUMENT', 'site_content');
define('TMPLVAR_CONTENT', 'site_tmplvar_contentvalues');
define('MODULE', 'site_modules');
define('CHUNK', 'site_htmlsnippets');
define('PLUGIN', 'site_plugins');
define('SNIPPET', 'site_snippets');
define('TEMPLATE', 'site_templates');
define('TMPLVAR', 'site_tmplvars');

define('REVISION_PATH', 'http://' . $_SERVER['SERVER_NAME'] . '/assets/plugins/revision/revision.php');

$id = isset($_REQUEST['id']) ? addslashes($_REQUEST['id']) : $id;
$rev_action = addslashes($_REQUEST['rev_action']);
$rev_type = addslashes($_REQUEST['type']);
$editedon = addslashes($_REQUEST['editedon']);

if (!(function_exists('createRevision'))) {

     function createRevision($id, $type) {
          global $modx;
          switch ($type) {
               case DOCUMENT:
               case MODULE:
               case CHUNK:
               case PLUGIN:
               case SNIPPET:
               case TEMPLATE:
                    $query = "INSERT INTO rev_$type SELECT * FROM " . $modx->getFullTableName($type) . " WHERE id=$id";
                    $modx->db->query($query);

                    if ($modx->db->getLastError()) {
                         throw new Exception("Could not create revision for ID: $id<br/>Query: $query<br/>Error: " . $modx->db->getLastError());
                    }

                    // The following will be added at a later time.
                    /* if ($type == DOCUMENT) {
                      // copy the {prefix}_site_tmplvar_contentvalues
                      $query = "INSERT INTO rev_" . TMPLVAR_CONTENT . " SELECT * FROM " . $modx->getFullTableName(TMPLVAR_CONTENT) . " WHERE id=$id";
                      $modx->db->query($query);
                      if ($modx->db->getLastError()) {
                      throw new Exception("Could not create content revision for ID: $id <br/>Query: $query<br/> Error: " . $modx->db->getLastError());
                      }
                      }

                      if ($type == TEMPLATE) {
                      // copy the {prefix}_site_tmplvar_contentvalues
                      $query = "INSERT INTO rev_" . TMPLVAR . " SELECT * FROM " . $modx->getFullTableName(TMPLVAR) . " WHERE id=$id";
                      $modx->db->query($query);
                      if ($modx->db->getLastError()) {
                      throw new Exception("Could not create content revision for ID: $id <br/>Query: $query<br/> Error: " . $modx->db->getLastError());
                      }
                      } */
                    break;
               default:
          }
     }

}

if (!(function_exists('createRevisionTab'))) {

     function createRevisionTab($type, &$event, $id) {
          global $modx;
          // Create a tab to be displayed for revisions.
          $html = '<div class="tab-page" id="tabRevision"><h2 class="tab">Revisions</h2><table><thead><tr><td><strong>Revision ID</strong</td><td><strong>Changed By</strong></td><td><strong>Changed On</strong></td><td><strong>View</strong></td><td><strong>Revert</strong></td></tr></thead><tbody>';

          switch ($type) {
               case DOCUMENT:
                    $query = "SELECT * FROM rev_" . $type . " JOIN " . $modx->getFullTableName('manager_users') . " ON rev_" . $type . ".editedby=" . $modx->getFullTableName('manager_users') . ".id WHERE rev_" . $type . ".id=$id";
                    $js = '<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabRevision" ) );</script>';
                    $rs = $modx->db->query($query);

                    if (!($rs)) {
                         throw new Exception('Unable to retrieve revision listing: ' . $modx->db->getLastError());
                    }

                    if ($modx->db->getRecordCount($rs) <= 0) {
                         $html .= '<tr><td colspan="4" align="center"><strong>No Revisions</strong></td></tr>';
                    } else {
                         while ($row = $modx->db->getRow($rs, 'assoc')) {
                              $username = isset($row['username']) ? $row['username'] : 'NA';
                              $html .= '<tr><td>' . $row['editedon'] . '</td><td>' . $username . '</td><td>' . date('F d, Y @ g:i:s a', $row['editedon']) . '</td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=view&type=' . $type . '&editedon=' . $row['editedon'] . '" rel="lightbox[inline 360 180]" target="_blank">View</a></td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=revert&type=' . $type . '&editedon=' . $row['editedon'] . '" target="_blank">Revert</a></td></tr>';
                         }
                    }
                    break;
               case MODULE:
                    $query = "SELECT * FROM rev_" . $type;
                    $rs = $modx->db->query($query);
                    if (!($rs)) {
                         throw new Exception('Unable to retrieve revision listing: ' . $modx->db->getLastError() . $query);
                    }

                    if ($modx->db->getRecordCount($rs) <= 0) {
                         $html .= '<tr><td colspan="4" align="center"><strong>No Revisions</strong></td></tr>';
                    } else {
                         $i = 0;
                         while ($row = $modx->db->getRow($rs, 'assoc')) {
                              $i++;
                              $html .= '<tr><td>' . $i . '</td><td>NA</td><td>NA</td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=view&type=' . $type . '&editedon=' . $i . '" rel="lightbox[inline 360 180]" target="_blank">View</a></td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=revert&type=' . $type . '&editedon=' . $i . '" target="_blank">Revert</a></td></tr>';
                         }
                    }
                    $js = ' <script type="text/javascript">document.getElementById("modulePane").appendChild(document.getElementById("tabRevision")); tpModule.addTabPage( document.getElementById( "tabRevision" ) );</script>';
                    break;
               case SNIPPET:
               case CHUNK:
               case TEMPLATE:
                    $query = "SELECT * FROM rev_" . $type;
                    $js = ' <script type="text/javascript">document.getElementById("modulePane").appendChild(document.getElementById("tabRevision")); tpModule.addTabPage( document.getElementById( "tabRevision" ) );</script>';

                    $rs = $modx->db->query($query);
                    if (!($rs)) {
                         throw new Exception('Unable to retrieve revision listing: ' . $modx->db->getLastError() . $query);
                    }

                    if ($modx->db->getRecordCount($rs) <= 0) {
                         $html .= '<tr><td colspan="4" align="center"><strong>No Revisions</strong></td></tr>';
                    } else {
                         $i = 0;
                         while ($row = $modx->db->getRow($rs, 'assoc')) {
                              $i++;
                              $html .= '<tr><td>' . $i . '</td><td>NA</td><td>NA</td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=view&type=' . $type . '&editedon=' . $i . '" rel="lightbox[inline 360 180]" target="_blank">View</a></td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=revert&type=' . $type . '&editedon=' . $i . '" target="_blank">Revert</a></td></tr>';
                         }
                    }
                    break;

               case PLUGIN:
                    $query = "SELECT * FROM rev_" . $type;
                    $js = '<script type="text/javascript">tpSnippet.addTabPage( document.getElementById( "tabRevision" ) );</script>';
                    $rs = $modx->db->query($query);
                    if (!($rs)) {
                         throw new Exception('Unable to retrieve revision listing: ' . $modx->db->getLastError() . $query);
                    }

                    if ($modx->db->getRecordCount($rs) <= 0) {
                         $html .= '<tr><td colspan="4" align="center"><strong>No Revisions</strong></td></tr>';
                    } else {
                         $i = 0;
                         while ($row = $modx->db->getRow($rs, 'assoc')) {
                              $i++;
                              $html .= '<tr><td>' . $i . '</td><td>NA</td><td>NA</td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=view&type=' . $type . '&editedon=' . $i . '" rel="lightbox[inline 360 180]" target="_blank">View</a></td><td><a href="' . REVISION_PATH . '?id=' . $id . '&rev_action=revert&type=' . $type . '&editedon=' . $i . '" target="_blank">Revert</a></td></tr>';
                         }
                    }
          }

          $html .= '</tbody></table></div>' . $js;

          $event->output($html);
     }

}

switch ($rev_action) {
     case 'view':
          if (!(empty($id)) && !(empty($rev_type)) && !(empty($editedon))) {
               $rt = @include_once($_SERVER['DOCUMENT_ROOT'] . '/manager/includes/config.inc.php');
               include_once($_SERVER['DOCUMENT_ROOT'] . '/manager/includes/document.parser.class.inc.php');
               $modx = new DocumentParser;
               switch ($rev_type) {
                    case DOCUMENT:
                         $query = "SELECT content FROM rev_$rev_type WHERE id=$id AND editedon=$editedon";
                         $rs = $modx->db->query($query);
                         $row = $modx->db->getRow($rs, 'assoc');
                         echo "<textarea style='width:100%; height:100%;'>" . htmlspecialchars($row['content']) . "</textarea>";
                         break;
                    case TEMPLATE:
                         $query = "SELECT content FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         echo "<textarea style='width:100%; height:100%;'>" . htmlspecialchars($row['content']) . "</textarea>";
                         break;
                    case CHUNK:
                    case SNIPPET:
                         $query = "SELECT snippet FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         echo "<textarea style='width:100%; height:100%;'>" . htmlspecialchars($row['snippet']) . "</textarea>";
                         break;
                    case MODULE:
                         $query = "SELECT modulecode FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         echo "<textarea style='width:100%; height:100%;'>" . htmlspecialchars($row['modulecode']) . "</textarea>";
                         break;
                    case PLUGIN:
                         $query = "SELECT plugincode FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         echo "<textarea style='width:100%; height:100%;'>" . htmlspecialchars($row['plugincode']) . "</textarea>";
                         break;

               }
          }
          break;
     case 'revert':
          if (!(empty($id)) && !(empty($rev_type)) && !(empty($editedon))) {
               $rt = @include_once($_SERVER['DOCUMENT_ROOT'] . '/manager/includes/config.inc.php');
               include_once($_SERVER['DOCUMENT_ROOT'] . '/manager/includes/document.parser.class.inc.php');
               $modx = new DocumentParser;
               switch ($rev_type) {
                    case DOCUMENT:
                         $query = "CREATE TEMPORARY TABLE t LIKE rev_$rev_type";
                         $modx->db->query($query);
                         $query = "INSERT INTO t SELECT DISTINCT * FROM rev_$rev_type WHERE id=$id AND editedon=$editedon";
                         $modx->db->query($query);
                         $query = "UPDATE " . $modx->getFullTableName($rev_type) . ",t SET " . $modx->getFullTableName($rev_type) . ".content=t.content WHERE " . $modx->getFullTableName($rev_type) . ".id=t.id;";
                         $modx->db->query($query);
                         $modx->logEvent(1, 1, $query, 'Revision - Revert');
                         if ($modx->db->getLastError()) {
                              throw new Exception("Error reverting to previous version. <br/>Query: $query");
                         }
                         $modx->clearCache();
                         echo "<h2>Reverted successfully. Close this window and re-edit the page.</h2>";
                         break;
                    case TEMPLATE:
                         $query = "SELECT content FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         $query = "UPDATE " . $modx->getFullTableName($rev_type) . " SET content='" . addslashes($row['content']) . "' WHERE id=$id";
                         $modx->db->query($query);
                         if ($modx->db->getLastError()) {
                              throw new Exception("Error reverting to previous version. <br/>Query: $query");
                         }
                         echo "<h2>Reverted successfully. Close this window and re-edit the template.</h2>";
                         break;
                    case SNIPEPT:
                    case CHUNK:
                         $query = "SELECT snippet FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         $query = "UPDATE " . $modx->getFullTableName($rev_type) . " SET snippet='" . addslashes($row['snippet']) . "' WHERE id=$id";
                         $modx->db->query($query);
                         if ($modx->db->getLastError()) {
                              throw new Exception("Error reverting to previous version. <br/>Query: $query");
                         }
                         echo "<h2>Reverted successfully. Close this window and re-edit the snippet/chunk.</h2>";
                         break;
                    case MODULE:
                         $query = "SELECT modulecode FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         $query = "UPDATE " . $modx->getFullTableName($rev_type) . " SET modulecode='" . addslashes($row['modulecode']) . "' WHERE id=$id";
                         $modx->db->query($query);
                         if ($modx->db->getLastError()) {
                              throw new Exception("Error reverting to previous version. <br/>Query: $query");
                         }
                         echo "<h2>Reverted successfully. Close this window and re-edit the module.</h2>";
                         break;
                    case PLUGIN:
                         $query = "SELECT plugincode FROM rev_$rev_type WHERE id=$id";
                         $rs = $modx->db->query($query);
                         for ($i = 0; $i < $editedon; $i++)
                              $row = $modx->db->getRow($rs, 'assoc');
                         $query = "UPDATE " . $modx->getFullTableName($rev_type) . " SET plugincode='" . addslashes($row['plugincode']) . "' WHERE id=$id";
                         $modx->db->query($query);
                         if ($modx->db->getLastError()) {
                              throw new Exception("Error reverting to previous version. <br/>Query: $query");
                         }
                         echo "<h2>Reverted successfully. Close this window and re-edit the plugin.</h2>";
                         break;
               }
          }
          break;
     default:
          try {
               switch ($event->name) {
                    case "OnChunkFormRender":
                         createRevisionTab(CHUNK, $event, $id);
                         break;
                    case "OnModFormRender":
                         createRevisionTab(MODULE, $event, $id);
                         break;
                    case "OnPluginFormRender":
                         createRevisionTab(PLUGIN, $event, $id);
                         break;
                    case "OnSnipFormRender":
                         createRevisionTab(SNIPPET, $event, $id);
                         break;
                    case "OnTempFormRender":
                         createRevisionTab(TEMPLATE, $event, $id);
                         break;
                    case "OnDocFormRender":
                         createRevisionTab(DOCUMENT, $event, $id);
                         break;
                    case "OnBeforeDocFormSave":
                    case "OnBeforeDocFormDelete":
                         createRevision($id, DOCUMENT);
                         break;
                    case "OnBeforeModFormSave":
                    case "OnBeforeModFormDelete":
                         createRevision($id, MODULE);
                         break;
                    case "OnBeforePluginFormSave":
                    case "OnBeforePluginFormDelete":
                         createRevision($id, PLUGIN);
                         break;
                    case "OnBeforeChunkFormSave":
                    case "OnBeforeChunkFormDelete":
                         createRevision($id, CHUNK);
                         break;
                    case "OnBeforeSnippetFormSave":
                    case "OnBeforeSnippetFormDelete":
                         createRevision($id, SNIPPET);
                         break;
                    case "OnBeforeTempFormSave":
                    case "OnBeforeTempFormDelete":
                         createRevision($id, TEMPLATE);
                         break;
               }
               if (isset($revPermissions)) {
                    $users = explode(',', $revPermissions);
                    if (!(in_array($modx->getLoginUserID(), $users)))
                         break;
               }
          } catch (Exception $ex) {
               $modx->logEvent(1, 3, $ex->getMessage(), "Revision - Plugin");
          }
}
