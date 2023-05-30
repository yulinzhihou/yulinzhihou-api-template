<?php
declare (strict_types = 1);

namespace app\command;

use ParagonIE\EasyRSA\KeyPair;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Env;

class Key extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('key:generate')
            ->setDescription('Generate a New RSA Private and Public key')
            ->addOption('force', '-f', Option::VALUE_NONE, '--force, -f : Force To Generate A New RSA Private and Public key ');
    }

    /**
     * 生成
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     * @throws \ParagonIE\EasyRSA\Exception\InvalidKeyException
     */
    protected function execute(Input $input, Output $output)
    {
        $keyPair = KeyPair::generateKeyPair(4096);
        $secretKey = $keyPair->getPrivateKey()->getKey();
        $publicKey = $keyPair->getPublicKey()->getKey();
        $path = root_path().DIRECTORY_SEPARATOR.'certs';
        if (is_dir($path)) {
            @mkdir($path,0777,true);
        }
        $seKey = $path.DIRECTORY_SEPARATOR.Env::get('jwt.name','yulinzhihou').'.key';
        $pubKey = $path.DIRECTORY_SEPARATOR.Env::get('jwt.name','yulinzhihou').'.pem';
        if ($input->hasOption('force')) {
            if (!is_dir(dirname($seKey))) {
                @mkdir(dirname($seKey),0777,true);
            }
            file_put_contents($seKey,$secretKey);
            file_put_contents($pubKey,$publicKey);
        } else {
            if (!file_exists($seKey) || !file_exists($pubKey)) {
                if (!is_dir(dirname($seKey))) {
                    @mkdir(dirname($seKey),0777,true);
                }
                file_put_contents($seKey,$secretKey);
                file_put_contents($pubKey,$publicKey);
            }
        }

        $output->writeln("Key Generate Success! Path: ".root_path().Env::get('jwt.cert_path').DIRECTORY_SEPARATOR);
    }
}
