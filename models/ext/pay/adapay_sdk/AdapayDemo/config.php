<?php
/**
 * init方法参数介绍
 * 第一个是配置文件路径或者配置数组对象
 * 第二个参数是SDK模式
 * 第三个是标识第一个参数的类型 true为数组对象 false为文件路径
 **/
/**
 * $config_object = [
 *    "api_key_live" => "api_live_9c14f264-e390-41df-984d-df15a6952031",
 *    "rsa_private_key" => "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMQhsygJ2pp4nCiDAXiqnZm6AzKSVAh+C0BgGR6QaeXzt0TdSi9VR0OQ7Qqgm92NREB3ofobXvxxT+wImrDNk6R6lnHPMTuJ/bYpm+sx397rPboRAXpV3kalQmbZ3P7oxtEWOQch0zV5B1bgQnTvxcG3REAsdaUjGs9Xvg0iDS2tAgMBAAECgYAqGFmNdF/4234Yq9V7ApOE1Qmupv1mPTdI/9ckWjaAZkilfSFY+2KqO8bEiygo6xMFCyg2t/0xDVjr/gTFgbn4KRPmYucGG+FzTRLH0nVIqnliG5Ekla6a4gwh9syHfstbOpIvJR4DfldicZ5n7MmcrdEwSmMwXrdinFbIS/P1+QJBAOr6NpFtlxVSGzr6haH5FvBWkAsF7BM0CTAUx6UNHb+RCYYQJbk8g3DLp7/vyio5uiusgCc04gehNHX4laqIdl8CQQDVrckvnYy+NLz+K/RfXEJlqayb0WblrZ1upOdoFyUhu4xqK0BswOh61xjZeS+38R8bOpnYRbLf7eoqb7vGpZ9zAkEAobhdsA99yRW+WgQrzsNxry3Ua1HDHaBVpnrWwNjbHYpDxLn+TJPCXvI7XNU7DX63i/FoLhOucNPZGExjLYBH/wJATHNZQAgGiycjV20yicvgla8XasiJIDP119h4Uu21A1Su8G15J2/9vbWn1mddg1pp3rwgvxhw312oInbHoFMxsQJBAJlyDDu6x05MeZ2nMor8gIokxq2c3+cnm4GYWZgboNgq/BknbIbOMBMoe8dJFj+ji3YNTvi1MSTDdSDqJuN/qS0="
 * ];
 * \AdaPay\AdaPay::init($config_object, "live", true);
 **/
$pri_key       = 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJelNFZrODhiZ94uHM1kNwfqf324+xQ/6rJSlZ9cvFoguGPotCnkpgYLzRsQnI9xNAVIYzOL5rdmjpjvmeI86dVvZG5MWqYM6XxwR96oN5Rwv3l56XWKmkNpp5PESheYx+McX70uxIStdfOWCjcFjMlGIuBQ6Jk3CRKhAcLGIimxAgMBAAECgYAH6gEGfo9/T9FsZLsvk+qUUO5o5QnDd4d72XgCCmCxEnKVEjyu0AZDHAQPBMmq6cVFfk7hDozSpvlLrXt6NWQdoJjCkSy8Wb/oAv3vIEG35d/cAsC+1KhSC/TQHTSe1RdJp2DJ2tr1TFm8SivPPvILKiHVhv+jYtBpfL5K53atPQJBAPepz9X2HEkHHFT6AmG4rl+gFO/kOGfW9rJ9wK6UFxykjBwdu4HcME7RdgIHsIA0a0avqUEWIu8nBve08nNXv90CQQCcv/ojSdWID60weLiTwDYERFH8sAlUTRlPtvu1FTmPRYItE90jf/+LHl7cIzP2ebj7RwFVTnAvKSXKXLB0zZ3lAkB7pQTFLlTaxLkJV7P+s6Qiy5oIViku9mw9mniq7/ZF74SfuNxXLeXUQ9ClnM8qgoEYTjSy8OlJ+nMJEgaFPUblAkAPNPi5b7JNjufIag3OF7ml1FL35sQg75HjD+d/e92nrqCgaurrRMGv53RgevHRmaF+jzbr5b4wdRd7eF6OFlyZAkEA8DSToO8/gOMMbKXPVa6QhGB+Ekd/rH7fZXm5KWiIZqn10+2L4EadiDuPMf3EX76HRkwi1npeitVP9LLX3og/tA==';
$pub_key       = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCXpTRWazg4YmfeLhzNZDcH6n99uPsUP+qyUpWfXLxaILhj6LQp5KYGC80bEJyPcTQFSGMzi+a3Zo6Y75niPOnVb2RuTFqmDOl8cEfeqDeUcL95eel1ippDaaeTxEoXmMfjHF+9LsSErXXzlgo3BYzJRiLgUOiZNwkSoQHCxiIpsQIDAQAB';//我们的
$pub_key       = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCwN6xgd6Ad8v2hIIsQVnbt8a3JituR8o4Tc3B5WlcFR55bz4OMqrG/356Ur3cPbc2Fe8ArNd/0gZbC9q56Eb16JTkVNA/fye4SXznWxdyBPR7+guuJZHc/VW2fKH2lfZ2P3Tt0QkKZZoawYOGSMdIvO+WqK44updyax0ikK6JlNQIDAQAB';
$pri_key       = chunk_split($pri_key, 64, "\n");
$pub_key       = chunk_split($pub_key, 64, "\n");
$pri_key       = "-----BEGIN RSA PRIVATE KEY-----\n$pri_key-----END RSA PRIVATE KEY-----\n";
$pub_key       = "-----BEGIN PUBLIC KEY-----\n$pub_key-----END PUBLIC KEY-----\n";
$config_object = [
    "api_key_live"    => "api_live_c06e16b7-6d06-4509-8f36-d3a1ef655221",
    "rsa_private_key" => $pri_key,
    "rsa_public_key" => $pub_key,

];
\AdaPay\AdaPay::init(dirname(__FILE__) . '/config/config.json', "live", false);