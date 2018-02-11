<?php if (!defined('THINK_PATH')) exit();?>
<?php if(is_array($method["fields"])): foreach($method["fields"] as $key=>$item): if(empty($item["remark"])): ?><input type="hidden" name="<?php echo ($item["name"]); ?>" value="<?php echo ($item["value"]); ?>"><?php endif; ?>
    <?php if(!empty($item["remark"])): ?><tr>
            <td><?php echo ($item["name"]); ?>:</td>
            <td class="center">
                <div style="width:85%;margin:5px">
                    <input type="text" style="width:520px;"  name="<?php echo ($item["name"]); ?>" placeholder="<?php echo ($item["remark"]); ?>">
                </div>
            </td>
        </tr><?php endif; endforeach; endif; ?>
<tr>
    <td class="center">
        <div style="width:85%;margin:5px">
            <input type="submit" value="执行"  class="button small">
        </div>
    </td>
</tr>

<input type="hidden" name="key" value="<?php echo ($method["key"]); ?>">