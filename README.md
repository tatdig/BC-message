# BC-message

This may help to "copywrite" matters, by putting extra layer of security.

Example of saving messages to blockchain TDCoin.

Install or compile TDCoinCore from https://github.com/tatdig/TDCoinCore.

Start TDCoinCore.  Create receiving address.

Before using this script, make few payments with sufficient amount to your wallet address!
For example 100 payments with 1TDC each. After at least 2 new blocks (please refer to source code) in
TDCoin blockchain, you can use it to send messages.

Text to send goes into text.txt file.

Make sure to run script as same user as TDCoinCore for example:
  runuser -l someuser -c "php /home/someuser/BC-short-message/send2bc.php yourwalletaddresshere"

To view transaction use TDCoin blockchain explorer:

https://www.tdcoincore.org/bcexplorer/tx/7cd2703818b05a677c94670b29c9d7965b69c044270ee6c286c113993af94d1d

https://www.tdcoincore.org/bcexplorer/tx/32c7f5d33821dafe6140aa9caf325da7fb7b645d1a4de28d87674e438b7c7fdc

https://www.tdcoincore.org/bcexplorer/tx/3847cb815a151dee03d0981b6da6229c69bfd9f99b32f5915489d4540624ec3a
