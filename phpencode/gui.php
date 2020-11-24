<html>
<head>
<style>
tr.line td {padding-top:10px;}
td.info {font-size:-1;color:#999;}
</style>
</head>
<body>
<?php
	set_time_limit(0);
	if (''==@$_REQUEST['extensions']) $_REQUEST['extensions'] = 'php';
	$ext = @$_REQUEST['ext'];
	$src = @$_REQUEST['src'];
	$dst = @$_REQUEST['dst'];

	(''==$ext) && ($ext = 'php');
	if (get_magic_quotes_gpc()){
		$src = stripslashes($src);
		$dst = stripslashes($dst);
	}
?>
<form action="gui.php">
<table cellpadding=0 cellspacing=0 border=0>
	<tr class="line"><td>Kaynak Dizin</td> <td align=center>:</td>
		<td><input type="text" name="src" value="<?php echo $src ?>"></td>
	<tr><td></td> 

	<tr class="line"><td>Hedef Dizin</td> <td align=center>:</td>
		<td><input type="text" name="dst" value="<?php echo $dst ?>"></td>
	<tr><td></td> 

	<tr class="line"><td>Uzantı</td> <td align=center>:</td>
		<td><input type="text" name="ext" value="<?php echo $ext ?>"></td>
	<tr><td></td> 

	<tr class="line"><td></td> <td></td>
		<td><button type="submit">Kriptola</button></td>
</table>
</form>

<?php
function remove_utf8_bom($text)
{
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}
?>

<?php
function ytcrypt_encode($src, $dst, $extensions)
{
	if ($h = opendir($src)) {
		$dirs = array();
		while (false !== ($f = readdir($h)))
		{
			if (('.'==$f) || ('..'==$f)) continue;
			$source = $src .'/'.$f;
			if (is_dir($source)) {
				$dirs[] = $f;
			} else {
				$pos = strrpos($f,'.');
				if (false !== $pos){
					$ext = strtolower(substr($f, $pos+1));
					if (in_array($ext, $extensions)){
						echo '<br>Kriptolandı: ',$source;
						$a=file_get_contents($source);			
						$b=kriptola($a,$dst.'/'.$f);						
						continue;
					}
				}
				copy($source, $dst.'/'.$f);
			}
		}
		closedir($h);
//		return;

		foreach ($dirs as $dir) {
			$target = $dst . '/' . $dir;
			if (!is_dir($target)){
				if (!mkdir($target)) {
					echo '<br>Dizin Oluşturulamıyor... "',$target,'"';
					continue;
				}
			}
			ytcrypt_encode($src.'/'.$dir, $target, $extensions);
		}
	} else {
		echo '<br>Dizin Okunamıyor... "',$src,'"';
	}
}

if (''!=$src) {
	$err = array();
	$src = realpath($src);

	if (''==$src) $err[] = 'Kaynak Dizin Hatalı...';
	if (0==strlen($dst)) {
		$err[] = 'Hedef Dizin Hatalı';
	} else {
		$dst = realpath($dst);
		if (0==strlen($dst)) $err[] = 'Hedef Dizin Hatalı';
	}
	if (0==strlen($ext)) {
		$err[] = 'Dosya Uzantısı Hatalı';
	} else {
		$extensions = array();
		$ext = explode(',',$ext);
		for ($i=count($ext)-1; $i>=0; $i--) {
			$str = trim($ext[$i]);
			if (strlen($str)){
				$extensions[] = strtolower($str);
			}
		}
	}
	if (count($err)){
		echo '<ul>Hata';
		foreach ($err as $msg)
			echo '<li>',$msg,'</li>';
		echo '</ul>';
	} else {
		ytcrypt_encode($src, $dst, $extensions);
	}
}
?>
</body>
</html>