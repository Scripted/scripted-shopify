<div class="col-md-12">
<h1>Current Jobs 
    <a href="<?php echo site_url('scripted-create-job');?>" style="font-size: 16px;"> - Create a Job</a>
</h1>
<?php

$paged            = (isset($_GET['paged']) and $_GET['paged'] !='') ? $this->input->get('paged') : 1;
$per_page         = 10;    
    
$totalProjects  = ($allJobs) ? count($allJobs) : 0;
$totalPages     = ceil($totalProjects/$per_page);
$out = '';
if($allJobs)
    $allJobs = array_slice($allJobs, ($paged == 1) ? 0: ($paged-1) * $per_page, $per_page);

// paggination

$paggination = '';

 $pageOne = '';         
 if($totalPages < 2)
     $pageOne = ' one-page';     

 $paggination .='<div class="text-center">
    <ul class="pagination">';

        
        $prePage = '';
        if($paged < 2) 
            $prePage = ' class="disabled"';
        $nextPage = '';
        if($totalPages == $paged) 
            $nextPage = ' class="disabled"';

        $preLink = 1;
        if($paged > 1) 
            $preLink = $paged-1;
        $nextLink = $totalPages;
        if($paged < $totalPages) 
            $nextLink = $paged+1;

        $paggination .='<li'.$prePage.'><a href="'.base_url().'?paged=1" title="Go to the first page">&laquo;</a></li>
                <li'.$prePage.'><a href="'.base_url().'?paged='.$preLink.'" title="Go to the previous page">&lsaquo;</a> </li>
                   <li><span>'.$paged.' of '.$totalPages.' ('.$totalProjects.' items)</span></li>
                    <li'.$nextPage.'><a href="'.base_url().'?paged='.$nextLink.'" title="Go to the next page">&rsaquo;</a></li>
                <li'.$nextPage.'><a href="'.base_url().'?paged='.$totalPages.'" title="Go to the last page">&raquo;</a></li>';

           $paggination .='
     </ul>
    <br class="clear">
    </div>';
// paggination end


$out .='<div class="table-responsive"><table cellspacing="0" class="table">
            <thead>
                <tr>
                <th scope="col" width="40%"><span>Topic</span></th>
                <th scope="col" width="10%"><span>Quantity</span></th>
                <th scope="col" width="15%"><span>State</span></th>
                <th scope="col" width="15%"><span>Deadline</span></th>
                <th scope="col" width="20%"></th>
                </tr>
            </thead>
              <tbody>';

if($allJobs)  {           
    $i = 1;
    foreach($allJobs as $job) {
        $out .='<tr valign="top" class="scripted">
            <input type="hidden" id="project_'.$i.'" value="'.$job->id.'">
            <td>'.$job->topic.'</td>
            <td>'.$job->quantity.'</td>
            <td>'.$job->state.'</td>
            <td>'.date('F j', strtotime($job->deadline_at)).'</td>';

            $out .='<td>';
            if($job->state == 'ready for review') {
                $out .= '<a id="accept_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Accept\')">Accept</a> | ';
                $out .= '<a id="request_'.$job->id.'" href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Request\')"  title="'.strip_tags(substr($job->topic,0,50)).'">Request Edits</a>';
            }elseif($job->state == 'ready for acceptance') {
                $out .= '<a id="accept_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Accept\')">Accept</a> | ';
                $out .= '<a id="reject_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Reject\')">Reject</a>';
            }elseif ($job->state == 'accepted') {
                $out .= '<a id="create_'.$job->id.'" href="javascript:void(0)"  onclick="finishedProjectActions(\''.$job->id.'\',\'Create\')">Create Draft</a> | ';
                $out .= '<a id="request_'.$job->id.'" href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Request\')"  title="'.strip_tags(substr($job->topic,0,50)).'">Request Edits</a>';
            }
            
            $out .='</td>';
            $out .='</tr>';
            $i++;
    }

} else {
    $out .='<tr valign="top">
            <th colspan="5"  style="text-align:center;" class="check-column"><strong>Your Scripted account has no Current Jobs. <a href="'.site_url('scripted-create-job').'">Create a Job</a></strong></td>
            </tr>';
}

 $out .= '</tbody>
        </table></div>'; // end table

 echo $out.$paggination;
 ?>
<script>
    function finishedProjectActions(proId,actions) {
            
           if(actions == 'Accept')
                jQuery("#accept_"+proId).html('Accepting...'); 
           else if(actions == 'Reject')
                jQuery("#reject_"+proId).html('Rejecting...'); 
           else if(actions == 'Create')
                jQuery("#create_"+proId).html('Creating...'); 
           else if(actions == 'View')
                jQuery("#view_"+proId).html('Loading...'); 
            else if(actions == 'Request')
                jQuery("#request_"+proId).html('Loading...'); 
                
            jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo site_url('scripted_actions');?>?do='+actions+'&project_id='+proId,
                    data: '',
                    success: function(data) {     
                        
                        if(actions == 'Accept')
                            jQuery("#accept_"+proId).html(data); 
                       else if(actions == 'Reject')
                            jQuery("#reject_"+proId).html(data); 
                       else if(actions == 'Create')
                            jQuery("#create_"+proId).html(data); 
                       else if(actions == 'Request') {
                            jQuery("#model_body_text").html(data); 
                            jQuery('#scriptedModal').modal();
                            jQuery("#request_"+proId).html('Request Edits');
                        } else if(actions == 'View') {
                            jQuery("#view_"+proId).html('View'); 
                            jQuery("#model_body_text").html(data); 
                            jQuery('#scriptedModal').modal();
                        }
                    }
                });
       }
       
</script>
<!-- Modal -->
<div class="modal fade" id="scriptedModal" tabindex="-1" role="dialog" aria-labelledby="scriptedModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Scripted</h4>
      </div>
        <div class="modal-body" id="model_body_text">
        
       </div>
    </div>
  </div>
</div>
</div>
