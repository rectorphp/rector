<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipAssignInLoopWithIndirectReturn
{
    public function updateArtworkStatus(): Release
    {
        // Assume we start completed
        $status = Release::ARTWORK_STATUS_COMPLETE;
        $artwork = new Artwork($this);
        foreach ($artwork->specs as $key => $pdf) {
            if ($this->shouldHaveArtwork($key) && !$this->hasArtwork($key)) {
                $status = Release::ARTWORK_STATUS_INCOMPLETE;
            }
        }

        if ($this->artwork_status != $status) {
            $this->artwork_status = $status;
            $this->artwork_status_message = null;
        }

        return $this;
    }
}

?>
