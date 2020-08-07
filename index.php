<?php

$in = json_decode(file_get_contents('php://input'), true);
error_log(var_export($in, true));

$code = $in['code'];

if (substr($code, -1) !== ';') {
  $code .= ';';
}
error_log(var_export($code, true));

$response = [
  'err' => false,
];

$tokens = array_slice(token_get_all("<?php $code"), 1);

//$response['tokens'] = $tokens;

$exprs = array_reduce($tokens, function($exprs, $token) {
  // append this token to the last expression
  $exprs[count($exprs)-1] .= is_array($token) ? $token[1] : $token;
  // start new expr after a semicolon
  if ($token === ';') {
    $exprs[] = '';
  }
  return $exprs;
}, ['']);

//$response['expressions'] = $exprs;

try {
  ob_start();
  eval($code);
  $response['result'] = ob_get_clean();
  error_log($response['result']);
} catch (Exception $e) {
  $response['err'] = $e->getMessage();
}

echo json_encode($response);
