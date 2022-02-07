public static function endpoint($serviceId,$partitionXid,$useXidAttrs=['cid','sid'],$altCfg=NULL,$epSuffix='')
    {
        if (is_object($partitionXid)) $partitionXid = (array)$partitionXid; 
        else if (is_string($partitionXid)) {
            if (($partitionXid[0]!=='{')&&($partitionXid[0]!=='[')) $partitionXid = base64_decode($partitionXid);
            $partitionXid = json_decode($partitionXid,TRUE);
        }
        for ($attrIdx=0,$endpointPrefix=''; (($attrIdx<count($useXidAttrs))&&isset($partitionXid[$useXidAttrs[$attrIdx]])); $attrIdx++) {
            $endpointPrefix = $partitionXid[$useXidAttrs[$attrIdx]].'.'.$endpointPrefix;
        }
        $endpoint = !empty($altCfg) ? $altCfg['services'][$serviceId]['endpoint'] : config('services.'.$serviceId.'.endpoint');
        $endpoint = 'https://'.$endpointPrefix.$endpoint.$epSuffix;
        return $endpoint;
    }
	
	
	
	
	public function getIncorporationInvites($incorporationId)
    {
        $retTokens = [];
        if (isset($user['incorporation']['id'])) {
            $data = [
                'roleId' => \App\Logic\Incorporation::ROLEID_ACCOUNT_HOLDER,
                'user' => $user,
                'userPartitionXid' => $user['partitionXid'],
            ];
            $this->userIncorporation->createIncorporationRole($user['id'], $user['incorporation']['id'], $data);
        }
        // dd($incorporationId);
        // echo "SELECT id, data, EXTRACT(EPOCH FROM created) AS created FROM \"UserToken\" WHERE data->>'type'=? AND data->'invite'->>'incorporation_id'=? ORDER BY created DESC",
        // [\App\Repositories\UserRepository::TOKEN_TYPE_ADDTOORGANISATION, $incorporationId];exit;
        $tokens = $this->db->select("SELECT id, data, EXTRACT(EPOCH FROM created) AS created FROM \"UserToken\" WHERE data->>'type'=? AND data->'invite'->>'incorporation_id'=? ORDER BY created DESC",
            [\App\Repositories\UserRepository::TOKEN_TYPE_ADDTOORGANISATION, $incorporationId]);            
            
        if (!empty($tokens[0])) {
            foreach ($tokens as $token) {
                if (!empty($token->data)) {
                    $retToken = self::json_decode_sc2cc($token->data, ['id' => $token->id, 'created' => $token->created]);
                    $retToken['user'] = $retToken['user'];
                    $retTokens[] = $retToken;
                }
            }
        }

        return $retTokens;
    }
