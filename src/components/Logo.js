import { forwardRef } from 'react';
import PropTypes from 'prop-types';
import { Box } from '@mui/material';

const Logo = forwardRef(({ disabledLink = false, sx, ...other }, ref) => {
  const logo = (
    <Box
      ref={ref}
      component="img"
      src="/static/logo.svg"
      sx={{ width: 40, height: 40, cursor: 'pointer', ...sx }}
      {...other}
    />
  );

  if (disabledLink) {
    return <>{logo}</>;
  }

  return <>{logo}</>;
});

Logo.propTypes = {
  sx: PropTypes.object,
  disabledLink: PropTypes.bool,
};

export default Logo; 